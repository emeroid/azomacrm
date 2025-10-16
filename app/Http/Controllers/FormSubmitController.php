<?php

namespace App\Http\Controllers;

use App\Models\FormTemplate;
use App\Models\FormSubmission;
use App\Models\Order;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FormSubmitController extends Controller
{
    /**
     * Finds or creates a draft submission, prioritizing unique identifiers (email/mobile) 
     * over the session ID.
     * * @param Request $request
     * @param string $slug
     * @return \Illuminate\Http\JsonResponse
     */
    public function saveDraft(Request $request, $slug)
    {
        $template = FormTemplate::where('slug', $slug)->firstOrFail();
        
        // Simple validation - only validate fields that have values
        $fieldRules = $this->getDraftValidationRules($template, $request->all());
        $validated = $request->validate($fieldRules);

        try {
            $sessionId = $request->session()->getId();
            $submission = null;
            
            // 1. Try to find an existing draft by Email (most unique identifier)
            if (!empty($validated['email'])) {
                $submission = FormSubmission::where('form_template_id', $template->id)
                    ->where('status', FormSubmission::STATUS_DRAFT)
                    ->whereJsonContains('data->email', $validated['email'])
                    ->first();
            }

            // 2. Fallback: Try to find by Mobile number
            if (!$submission && !empty($validated['mobile'])) {
                $submission = FormSubmission::where('form_template_id', $template->id)
                    ->where('status', FormSubmission::STATUS_DRAFT)
                    ->where(function ($query) use ($validated) {
                        // Check 'mobile' and 'phone' fields in the JSON data
                        $query->whereJsonContains('data->mobile', $validated['mobile'])
                              ->orWhereJsonContains('data->phone', $validated['mobile']);
                    })
                    ->first();
            }

            // 3. Final Fallback: Find or create by Session ID
            if (!$submission) {
                $submission = FormSubmission::firstOrCreate(
                    [
                        'form_template_id' => $template->id,
                        'session_id' => $sessionId,
                        'status' => FormSubmission::STATUS_DRAFT
                    ],
                    [
                        'data' => [],
                        'ip_address' => $request->ip(),
                        'user_agent' => $request->userAgent()
                    ]
                );
            }

            // Merge new data with existing data
            $currentData = $submission->data ?? [];
            $newData = array_merge($currentData, $validated);

            $submission->update([
                'data' => $newData,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'session_id' => $sessionId // Ensure session_id is always updated, just in case
            ]);

            return response()->json([
                'message' => 'Progress saved',
                'submission_id' => $submission->id,
                'saved_fields' => $validated
            ]);

        } catch (Exception $e) {
            Log::error("Error saving draft: " . $e->getMessage());
            return response()->json([
                'message' => 'Error saving progress'
            ], 500);
        }
    }

    /**
     * Handles the final form submission, completing an existing draft or creating a new one.
     * * @param Request $request
     * @param string $slug
     * @return \Illuminate\Http\JsonResponse
     */
    public function __invoke(Request $request, $slug)
    {
        $template = FormTemplate::where('slug', $slug)->firstOrFail();
        
        // Full validation on submission
        $validated = $request->validate($this->getValidationRules($template));
        
        try {
            $sessionId = $request->session()->getId();
            $draftSubmission = null;

            // 1. Try to find the draft by Email
            if (!empty($validated['email'])) {
                $draftSubmission = FormSubmission::where('form_template_id', $template->id)
                    ->where('status', FormSubmission::STATUS_DRAFT)
                    ->whereJsonContains('data->email', $validated['email'])
                    ->first();
            }

            // 2. Fallback: Try to find by Mobile number
            if (!$draftSubmission && !empty($validated['mobile'])) {
                $draftSubmission = FormSubmission::where('form_template_id', $template->id)
                    ->where('status', FormSubmission::STATUS_DRAFT)
                    ->where(function ($query) use ($validated) {
                        $query->whereJsonContains('data->mobile', $validated['mobile'])
                              ->orWhereJsonContains('data->phone', $validated['mobile']);
                    })
                    ->first();
            }

            // 3. Final Fallback: Find by Session ID
            if (!$draftSubmission) {
                $draftSubmission = FormSubmission::where('form_template_id', $template->id)
                    ->where('session_id', $sessionId)
                    ->where('status', FormSubmission::STATUS_DRAFT)
                    ->first();
            }
            
            if ($draftSubmission) {
                // Update the existing draft
                $draftSubmission->update([
                    'data' => $validated,
                    'status' => FormSubmission::STATUS_SUBMITTED,
                    'submitted_at' => now(),
                    'session_id' => $sessionId // Ensure session ID is updated
                ]);
                $submission = $draftSubmission;
            } else {
                // Create a new submission
                $submission = FormSubmission::create([
                    'form_template_id' => $template->id,
                    'data' => $validated,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'status' => FormSubmission::STATUS_SUBMITTED,
                    'submitted_at' => now(),
                    'session_id' => $sessionId
                ]);
            }

            // Create order if we have the required fields (same logic as before)
            if(isset($validated['fullname']) && isset($validated['products'])) {
                $order = Order::create([
                    'full_name' => $validated['fullname'],
                    'mobile' => $validated['mobile'],
                    'phone' => $validated['phone'] ?? $validated['tel'] ?? null,
                    'email' => $validated['email'] ?? null,
                    'address' => $validated['address'],
                    'state' => $validated['state'],
                    'marketer_id' => $template->user_id,
                    'source_type' => Order::SOURCE_TYPE_CUSTOMER,
                    'source_id' => $submission->id
                ]);

                // Product logic (ensure fields exist before using them)
                if (isset($validated['products']) && is_string($validated['products'])) {
                    $productParts = explode('::', $validated['products']);
                    if(count($productParts) >= 2) {
                        $selectedProductId = $productParts[0];
                        $selectedProductPrice = (float) $productParts[1];
                        $order->items()->create([
                            'unit_price' => $selectedProductPrice,
                            'product_id' => $selectedProductId,
                        ]);
                    }
                }
            }

            // Mark any other drafts for this session as abandoned
            FormSubmission::where('form_template_id', $template->id)
                ->where('session_id', $sessionId)
                ->where('status', FormSubmission::STATUS_DRAFT)
                ->where('id', '!=', $submission->id)
                ->update(['status' => FormSubmission::STATUS_ABANDONED]);

            return response()->json([
                'message' => 'Thank you for your submission!',
                'submission' => $submission,
                'redirect_url' => $template->redirect_url
            ]);

        } catch (Exception $e) {
            Log::error("Issue while creating order: " . $e->getMessage());
            return response()->json([
                'message' => 'Error while submitting form'
            ], 500);
        }
    }

    /**
     * Marks the current session's draft as abandoned.
     * * @param Request $request
     * @param string $slug
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAbandoned(Request $request, $slug)
    {
        $template = FormTemplate::where('slug', $slug)->firstOrFail();
        
        $sessionId = $request->session()->getId();
        
        // Mark all drafts for this session/template as abandoned
        $abandonedCount = FormSubmission::where('form_template_id', $template->id)
            ->where('session_id', $sessionId)
            ->where('status', FormSubmission::STATUS_DRAFT)
            ->update([
                'status' => FormSubmission::STATUS_ABANDONED,
                'abandoned_at' => now()
            ]);

        return response()->json([
            'message' => 'Form marked as abandoned',
            'abandoned_count' => $abandonedCount
        ]);
    }
    
    /**
     * Gets validation rules for final form submission.
     * * @param FormTemplate $template
     * @return array
     */
    protected function getValidationRules(FormTemplate $template)
    {
        $rules = [];
        // Note: $messages variable was defined but not returned or used in the original. 
        // Returning only $rules is standard for Illuminate\Http\Request::validate().
        
        foreach ($template->fields as $field) {
            $fieldRules = [];
            
            if ($field->is_required) {
                $fieldRules[] = 'required';
                // $messages[$field->name . '.required'] = 'The ' . strtolower($field->label) . ' field is required.';
            }
            
            switch ($field->type) {
                case 'email':
                    $fieldRules[] = 'email';
                    break;
                case 'number':
                    $fieldRules[] = 'numeric';
                    break;
                case 'date':
                    $fieldRules[] = 'date';
                    break;
                case 'tel':
                case 'mobile':
                    // Add basic validation for mobile/phone if needed
                    $fieldRules[] = 'string';
                    break;
                default:
                    $fieldRules[] = 'string';
                    break;
            }
            
            if (!empty($fieldRules)) {
                $rules[$field->name] = $fieldRules;
            }
        }
        
        return $rules;
    }

    /**
     * Gets validation rules for saving a draft. Uses 'sometimes' for all fields.
     * * @param FormTemplate $template
     * @param array $data
     * @return array
     */
    protected function getDraftValidationRules(FormTemplate $template, $data)
    {
        $rules = [];
    
        foreach ($template->fields as $field) {
            $fieldRules = ['sometimes'];
            
            // Apply type validation only if the field is present, not required
            switch ($field->type) {
                case 'text':
                case 'select':
                case 'radio':
                case 'checkbox':
                    $fieldRules[] = 'string';
                    break;
                case 'email':
                    $fieldRules[] = 'email';
                    break;
                case 'number':
                    $fieldRules[] = 'numeric';
                    break;
                case 'date':
                    $fieldRules[] = 'date';
                    break;
            }
            
            $rules[$field->name] = $fieldRules;
        }
        
        return $rules;
    }
}

// namespace App\Http\Controllers;

// use App\Models\FormTemplate;
// use App\Models\FormSubmission;
// use App\Models\Order;
// use Exception;
// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Log;

// class FormSubmitController extends Controller
// {
//     public function __invoke(Request $request, $slug)
//     {
//         $template = FormTemplate::where('slug', $slug)->firstOrFail();
        
//         $validated = $request->validate($this->getValidationRules($template));
        
//         try {
        
//             $submission = FormSubmission::create([
//                 'form_template_id' => $template->id,
//                 'data' => $validated,
//                 'ip_address' => $request->ip(),
//                 'user_agent' => $request->userAgent()
//             ]);

//             if($validated['fullname'] && $validated['products']) {

//                 $order = Order::create([
//                     'full_name' => $validated['fullname'],
//                     'mobile' => $validated['mobile'],
//                     'phone' => isset($validated['phone']) || isset($validated['tel']) ? $validated['phone'] : null,
//                     'email' => isset($validated['email']) ? $validated['email'] : null,
//                     'address' => $validated['address'],
//                     'state' => $validated['state'],
//                     'marketer_id' => $template->user_id,
//                     'source_type' => Order::SOURCE_TYPE_CUSTOMER,
//                     'source_id' => $submission->id
//                 ]);

//                 $productParts = explode('::', $validated['products']);


//                 if(count($productParts) >= 2) {
//                     $selectedProductId = $productParts[0];
//                     $selectedProductPrice = (float) $productParts[1]; // Cast to float for calculations        
//                     $order->items()->create([
//                         'unit_price' => $selectedProductPrice,
//                         'product_id' => $selectedProductId,
//                     ]);
//                 }
//             }

//             return response()->json([
//                 'message' => 'Thank you for your submission!',
//                 'submission' => $submission,
//                 'redirect_url' => $template->redirect_url
//             ]);

//         } catch (Exception $e) {
//             Log::error("Issue why creating order: " . $e);
//             return response()->json([
//                 'message' => 'Error while submitting form'
//             ], 500);

//         }
//     }
    
//     protected function getValidationRules(FormTemplate $template)
//     {
//         $rules = [];
//         $messages = [];
        
//         foreach ($template->fields as $field) {
//             $fieldRules = [];
            
//             if ($field->is_required) {
//                 $fieldRules[] = 'required';
//                 $messages[$field->name . '.required'] = 'The ' . strtolower($field->label) . ' field is required.';
//             }
            
//             switch ($field->type) {
//                 case 'email':
//                     $fieldRules[] = 'email';
//                     break;
//                 case 'number':
//                     $fieldRules[] = 'numeric';
//                     // if (isset($field->properties['min'])) {
//                     //     $fieldRules[] = 'min:' . $field->properties['min'];
//                     // }
//                     // if (isset($field->properties['max'])) {
//                     //     $fieldRules[] = 'max:' . $field->properties['max'];
//                     // }
//                     break;
//                 case 'date':
//                     $fieldRules[] = 'date';
//                     break;
//             }
            
//             if (!empty($fieldRules)) {
//                 $rules[$field->name] = $fieldRules;
//             }
//         }
        
//         return $rules;
//     }
// }