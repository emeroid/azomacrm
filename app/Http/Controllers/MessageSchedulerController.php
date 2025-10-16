<?php

namespace App\Http\Controllers;

use App\Models\FormTemplate;
use App\Models\ScheduledMessage;
use App\Models\Order;
use App\Models\TemplateField;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class MessageSchedulerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('Scheduler/Index', [
            // Assuming your User model has a hasMany(ScheduledMessage::class) relationship
            'messages' => auth()->user()->scheduledMessages()->get(), 
            
            // Data needed for the frontend to build the form
            'orderStatuses' => Order::getStatuses(),
            'userFormTemplates' => auth()->user()->formTemplates()->userForms(auth()->id())->get(['id', 'name']),
            'devices' => auth()->user()->whatsappDevices()->where('status', 'connected')->get(['id', 'name', 'session_id']),
        ]);
    }

    /**
     * Store a newly created scheduled message.
     */
    public function store(Request $request)
    {
        $action = $request->input('action');
        
        // --- 1. Base Validation ---
        $rules = [
            'device_id' => 'required|exists:whatsapp_devices,id',
            'action' => 'required|in:ORDER,FORM_SUBMISSION',
            'message' => 'required|string|max:10000',
            'send_at' => 'required|date',
        ];
        
        // --- 2. Dynamic Validation based on Action ---
        $targetCriteria = [];
        
        if ($action === 'ORDER') {
            $rules['order_criteria_type'] = 'required|in:ALL,STATUS';
            $rules['status'] = 'nullable|required_if:order_criteria_type,STATUS|in:' . implode(',', array_keys(Order::getStatuses()));
            
            $targetCriteria = $request->input('order_criteria_type') === 'ALL' 
                ? ['type' => 'ALL']
                : ['type' => 'STATUS', 'status' => $request->input('status'), 'order_id' => $request->input('order_id')];
            
            $actionType = Order::class;
            
        } elseif ($action === 'FORM_SUBMISSION') {
            $rules['form_template_id'] = 'required|exists:form_templates,id';
            $rules['whatsapp_field_name'] = 'required|string'; // User must select one
            
            $targetCriteria = ['form_template_id' => $request->input('form_template_id')];
            $actionType = \App\Models\FormSubmission::class;
        }

        $validated = $request->validate($rules);

        // --- 3. Validate WhatsApp Field Name for FORM_SUBMISSION (Completion) ---
        $whatsappFieldName = null;
        if ($action === 'FORM_SUBMISSION') {
            $templateId = $validated['form_template_id'];
            $fieldName = $validated['whatsapp_field_name'];
            $whatsappFieldName = $fieldName; // Use the validated field name

            // Check if the user-selected field name is actually a valid 'tel' or 'number' field
            $templateFieldExists = TemplateField::where('template_id', $templateId)
                                              ->where('name', $fieldName)
                                              ->whereIn('type', ['tel', 'number'])
                                              ->exists();
                                         
            if (!$templateFieldExists) {
                // If validation fails, redirect back with an error for the specific field
                return redirect()->back()->withErrors([
                    'whatsapp_field_name' => 'The selected field is not a valid phone or number input in the chosen form template.',
                ])->withInput();
            }
            
            // Add the field name to the criteria for later use by the scheduler worker
            $targetCriteria['whatsapp_field_name'] = $whatsappFieldName;
        }

        // --- 4. Create Scheduled Message ---
        auth()->user()->scheduledMessages()->create([
            'whatsapp_device_id' => $validated['device_id'],
            'action_type' => $actionType,
            'target_criteria' => $targetCriteria, // Target criteria now includes whatsapp_field_name
            'message' => $validated['message'],
            'send_at' => $validated['send_at'],
            'sent_count' => 0, // Will be gotten dynamically
            'failed_count' => 0 // Will be gotten dynamically
        ]);

        return redirect()->route('scheduler.index')->with('success', 'Message scheduled successfully!');
    }
    
    // ... (index and destroy methods remain similar, but target ScheduledMessage model)
    public function destroy(ScheduledMessage $scheduledMessage)
    {
        // Remember to add authorization policy check here:
        // $this->authorize('delete', $scheduledMessage); 
        $scheduledMessage->delete();
        return redirect()->back()->with('success', 'Scheduled message deleted!');
    }


    public function getPotentialWhatsappFields($templateId)
    {
        // Ensure the user owns the template before checking
        $template = auth()->user()->formTemplates()->userForms(auth()->id())->findOrFail($templateId);
    
        // Fetch ALL fields that are of type 'tel' or 'number'
        $potentialFields = TemplateField::where('template_id', $template->id)
                                      ->whereIn('type', ['tel', 'number'])
                                      // Select the name (key) and the label (display)
                                      ->get(['name', 'label']); 
    
        if ($potentialFields->isNotEmpty()) {
            return response()->json([
                'success' => true,
                'fields' => $potentialFields, // Return the list of potential fields
                'message' => 'Multiple potential WhatsApp fields found. Please select one.',
            ]);
        }
    
        return response()->json([
            'success' => false,
            'fields' => [],
            'message' => 'Error: This form template does not contain any Phone Input or Number field.',
        ], 422); 
    }
}
