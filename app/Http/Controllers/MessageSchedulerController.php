<?php

namespace App\Http\Controllers;

use App\Models\FormSubmission;
use App\Models\FormTemplate;
use App\Models\ScheduledMessage;
use App\Models\Order;
use App\Models\TemplateField;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class MessageSchedulerController extends Controller
{

    // A centralized list of supported dynamic placeholders for clarity
    protected const SUPPORTED_PLACEHOLDERS = [
        'ORDER' => [
            'order_id', 'order_number', 'customer_name', 'mobile', 'address', 'state', 
            'status', 'total_amount', 'product_name', 'created_at'
        ],
        'FORM_SUBMISSION' => [
            'submission_id', 'submitted_at', 'form_name', 'fullname_with_mobile', 
            // '... (plus any field name from the form data, e.g., mobile, email, etc.)'
        ],
    ];

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
            'supportedPlaceholders' => self::SUPPORTED_PLACEHOLDERS,

        ]);
    }

    public function create()
    {
        return Inertia::render('Scheduler/Create', [              
            // Data needed for the frontend to build the form
            'orderStatuses' => Order::getStatuses(),
            'userFormTemplates' => auth()->user()->formTemplates()->userForms(auth()->id())->get(['id', 'name']),
            'devices' => auth()->user()->whatsappDevices()->where('status', 'connected')->get(['id', 'name', 'session_id']),
            'supportedPlaceholders' => self::SUPPORTED_PLACEHOLDERS,
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
            'send_at' => 'required|date|after:now', 
            // New field for specific Order/Submission ID
            'target_id' => 'nullable|integer', 
        ];
        
        // --- 2. Dynamic Validation based on Action ---
        $targetCriteria = [];
        $actionType = null;
        
        if ($action === 'ORDER') {
            $rules['order_criteria_type'] = 'required|in:ALL,STATUS,SPECIFIC_ORDER';
            $rules['status'] = [
                'nullable', 
                'required_if:order_criteria_type,STATUS', 
                'required_if:order_criteria_type,SPECIFIC_ORDER',
                Rule::in(array_keys(Order::getStatuses()))
            ];

            // Validation for SPECIFIC_ORDER
            $rules['target_id'] = [
                'nullable',
                'required_if:order_criteria_type,SPECIFIC_ORDER',
                Rule::exists('orders', 'id')->where(function ($query) use ($request) {
                    // Check if the order belongs to the user AND has the selected status
                    // return $query->where('marketer_id', auth()->id())
                                 // ->where('status', $request->input('status'));
                }),
            ];
            
            $criteriaType = $request->input('order_criteria_type');
            $targetCriteria['type'] = $criteriaType;
            $actionType = Order::class;

            if ($criteriaType === 'STATUS' || $criteriaType === 'SPECIFIC_ORDER') {
                 $targetCriteria['status'] = $request->input('status');
                 
                 if ($criteriaType === 'SPECIFIC_ORDER') {
                     $targetCriteria['order_id'] = (int) $request->input('target_id');
                 }
            }
            
        } elseif ($action === 'FORM_SUBMISSION') {
            $rules['form_template_id'] = 'required|exists:form_templates,id';
            $rules['whatsapp_field_name'] = 'required|string'; 
            $rules['submission_criteria_type'] = 'required|in:ALL,SPECIFIC_SUBMISSION';

            // Validation for SPECIFIC_SUBMISSION
            $rules['target_id'] = [
                'nullable',
                'required_if:submission_criteria_type,SPECIFIC_SUBMISSION',
                Rule::exists('form_submissions', 'id')->where(function ($query) use ($request) {
                    // Ensure the submission exists for the selected template
                    return $query->where('form_template_id', $request->input('form_template_id'));
                }),
            ];

            $criteriaType = $request->input('submission_criteria_type');
            $targetCriteria['type'] = $criteriaType;
            $targetCriteria['form_template_id'] = (int) $request->input('form_template_id');
            $actionType = FormSubmission::class;

            if ($criteriaType === 'SPECIFIC_SUBMISSION') {
                $targetCriteria['form_submission_id'] = (int) $request->input('target_id');
            }
        }

        $validated = $request->validate($rules);
        
        // --- 3. Validate WhatsApp Field Name for FORM_SUBMISSION ---
        if ($action === 'FORM_SUBMISSION') {
            $templateId = $validated['form_template_id'];
            $fieldName = $validated['whatsapp_field_name'];

            // Check if the user-selected field name is actually a valid 'tel' or 'number' field
            $templateFieldExists = TemplateField::where('template_id', $templateId)
                                              ->where('name', $fieldName)
                                              ->whereIn('type', ['tel', 'number'])
                                              ->exists();
                                         
            if (!$templateFieldExists) {
                return redirect()->back()->withErrors([
                    'whatsapp_field_name' => 'The selected field is not a valid phone or number input in the chosen form template.',
                ])->withInput();
            }
            
            // Add the field name to the criteria for later use by the scheduler worker
            $targetCriteria['whatsapp_field_name'] = $fieldName;
        }

        // --- 4. Create Scheduled Message ---
        auth()->user()->scheduledMessages()->create([
            'whatsapp_device_id' => $validated['device_id'],
            'action_type' => $actionType,
            'target_criteria' => $targetCriteria, 
            'message' => $validated['message'],
            'send_at' => $validated['send_at'],
            'sent_count' => 0,
            'failed_count' => 0
            // sent_count and failed_count are now dynamic (will be set to 0 by default)
        ]);

        return redirect()->route('schedulers.index')->with('success', 'Message scheduled successfully!');
    }
    
    // -------------------------------------------------------------------------
    // AUXILIARY METHODS FOR FRONTEND SEARCH/FILTERING
    // -------------------------------------------------------------------------

    /**
     * Fetches potential WhatsApp fields from a specific Form Template.
     */
    public function getPotentialWhatsappFields($templateId)
    {
        // Ensure the user owns the template before checking
        $template = auth()->user()->formTemplates()->userForms(auth()->id())->findOrFail($templateId);
    
        $potentialFields = TemplateField::where('template_id', $template->id)
                                      ->whereIn('type', ['tel', 'number'])
                                      ->get(['name', 'label']); 
    
        if ($potentialFields->isNotEmpty()) {
            return response()->json([
                'success' => true,
                'fields' => $potentialFields, 
                'message' => 'Multiple potential WhatsApp fields found. Please select one.',
            ]);
        }
    
        return response()->json([
            'success' => false,
            'fields' => [],
            'message' => 'Error: This form template does not contain any Phone Input or Number field.',
        ], 422); 
    }

    /**
     * Searchable list of Orders by Status (For Enhanced ORDER Filtering)
     */
    public function getSearchableOrders(Request $request)
    {
        $request->validate([
            'status' => ['required', Rule::in(array_keys(Order::getStatuses()))],
            'search' => 'nullable|string|max:255',
        ]);

        $status = $request->input('status');
        $search = $request->input('search');

        $orders = Order::where('status', $status)
            ->where('marketer_id', auth()->id())
            ->when($search, function ($query, $search) {
                // Search by Order Number or Customer Name
                $query->where('order_number', 'like', "%{$search}%")
                      ->orWhere('full_name', 'like', "%{$search}%");
            })
            // Select necessary fields for display in the frontend search dropdown
            ->select('id', 'order_number', 'full_name', 'mobile', 'address') 
            ->limit(10)
            ->get();

        return response()->json($orders);
    }
    
    /**
     * Searchable list of Form Submissions by Template (For Enhanced FORM SUBMISSION Filtering)
     */
    public function getSearchableSubmissions(Request $request)
    {
        $request->validate([
            'form_template_id' => 'required|exists:form_templates,id',
            'search' => 'nullable|string|max:255',
        ]);

        $templateId = $request->input('form_template_id');
        $search = $request->input('search');

        // Ensure the user owns the template first
        $template = auth()->user()->formTemplates()->userForms(auth()->id())->findOrFail($templateId);

        $submissions = FormSubmission::where('form_template_id', $templateId)
            ->when($search, function ($query, $search) {
                // Search by ID or by a simple JSON contains check on the 'data' column
                $query->where('id', 'like', "%{$search}%")
                      // Check for search term within the serialized data
                      ->orWhereRaw('JSON_CONTAINS(data, ?)', ["\"%{$search}%\""]); 
            })
            // Select ID and a summary field for display
            ->select('id', 'created_at', DB::raw('SUBSTRING(JSON_EXTRACT(data, "$.fullname"), 1, 50) as submitter_name_hint')) 
            ->limit(10) 
            ->get();

        return response()->json($submissions);
    }

    // -------------------------------------------------------------------------
    // Destroy Method
    // -------------------------------------------------------------------------

    public function destroy(ScheduledMessage $scheduledMessage)
    {
        // Remember to add authorization policy check here:
        // $this->authorize('delete', $scheduledMessage); 
        $scheduledMessage->delete();
        return redirect()->back()->with('success', 'Scheduled message deleted!');
    }

    /**
     * Store a newly created scheduled message.
     */
    // public function store(Request $request)
    // {
    //     $action = $request->input('action');
        
    //     // --- 1. Base Validation ---
    //     $rules = [
    //         'device_id' => 'required|exists:whatsapp_devices,id',
    //         'action' => 'required|in:ORDER,FORM_SUBMISSION',
    //         'message' => 'required|string|max:10000',
    //         'send_at' => 'required|date',
    //     ];
        
    //     // --- 2. Dynamic Validation based on Action ---
    //     $targetCriteria = [];
        
    //     if ($action === 'ORDER') {
    //         $rules['order_criteria_type'] = 'required|in:ALL,STATUS';
    //         $rules['status'] = 'nullable|required_if:order_criteria_type,STATUS|in:' . implode(',', array_keys(Order::getStatuses()));
            
    //         $targetCriteria = $request->input('order_criteria_type') === 'ALL' 
    //             ? ['type' => 'ALL']
    //             : ['type' => 'STATUS', 'status' => $request->input('status'), 'order_id' => $request->input('order_id')];
            
    //         $actionType = Order::class;
            
    //     } elseif ($action === 'FORM_SUBMISSION') {
    //         $rules['form_template_id'] = 'required|exists:form_templates,id';
    //         $rules['whatsapp_field_name'] = 'required|string'; // User must select one
            
    //         $targetCriteria = ['form_template_id' => $request->input('form_template_id')];
    //         $actionType = \App\Models\FormSubmission::class;
    //     }

    //     $validated = $request->validate($rules);

    //     // --- 3. Validate WhatsApp Field Name for FORM_SUBMISSION (Completion) ---
    //     $whatsappFieldName = null;
    //     if ($action === 'FORM_SUBMISSION') {
    //         $templateId = $validated['form_template_id'];
    //         $fieldName = $validated['whatsapp_field_name'];
    //         $whatsappFieldName = $fieldName; // Use the validated field name

    //         // Check if the user-selected field name is actually a valid 'tel' or 'number' field
    //         $templateFieldExists = TemplateField::where('template_id', $templateId)
    //                                           ->where('name', $fieldName)
    //                                           ->whereIn('type', ['tel', 'number'])
    //                                           ->exists();
                                         
    //         if (!$templateFieldExists) {
    //             // If validation fails, redirect back with an error for the specific field
    //             return redirect()->back()->withErrors([
    //                 'whatsapp_field_name' => 'The selected field is not a valid phone or number input in the chosen form template.',
    //             ])->withInput();
    //         }
            
    //         // Add the field name to the criteria for later use by the scheduler worker
    //         $targetCriteria['whatsapp_field_name'] = $whatsappFieldName;
    //     }

    //     // --- 4. Create Scheduled Message ---
    //     auth()->user()->scheduledMessages()->create([
    //         'whatsapp_device_id' => $validated['device_id'],
    //         'action_type' => $actionType,
    //         'target_criteria' => $targetCriteria, // Target criteria now includes whatsapp_field_name
    //         'message' => $validated['message'],
    //         'send_at' => $validated['send_at'],
    //         'sent_count' => 0, // Will be gotten dynamically
    //         'failed_count' => 0 // Will be gotten dynamically
    //     ]);

    //     return redirect()->route('schedulers.index')->with('success', 'Message scheduled successfully!');
    // }
    
    // // ... (index and destroy methods remain similar, but target ScheduledMessage model)
    // public function destroy(ScheduledMessage $scheduledMessage)
    // {
    //     // Remember to add authorization policy check here:
    //     // $this->authorize('delete', $scheduledMessage); 
    //     $scheduledMessage->delete();
    //     return redirect()->back()->with('success', 'Scheduled message deleted!');
    // }


    // public function getPotentialWhatsappFields($templateId)
    // {
    //     // Ensure the user owns the template before checking
    //     $template = auth()->user()->formTemplates()->userForms(auth()->id())->findOrFail($templateId);
    
    //     // Fetch ALL fields that are of type 'tel' or 'number'
    //     $potentialFields = TemplateField::where('template_id', $template->id)
    //                                   ->whereIn('type', ['tel', 'number'])
    //                                   // Select the name (key) and the label (display)
    //                                   ->get(['name', 'label']); 
    
    //     if ($potentialFields->isNotEmpty()) {
    //         return response()->json([
    //             'success' => true,
    //             'fields' => $potentialFields, // Return the list of potential fields
    //             'message' => 'Multiple potential WhatsApp fields found. Please select one.',
    //         ]);
    //     }
    
    //     return response()->json([
    //         'success' => false,
    //         'fields' => [],
    //         'message' => 'Error: This form template does not contain any Phone Input or Number field.',
    //     ], 422); 
    // }
}
