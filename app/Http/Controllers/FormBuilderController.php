<?php

namespace App\Http\Controllers;

use App\Models\FormTemplate;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Models\TemplateField;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class FormBuilderController extends Controller
{
    public function index()
    {
        $templates = FormTemplate::query()
            ->where('is_template', true)
            ->withCount('fields')
            ->latest()
            ->get();

        return Inertia::render('FormBuilder/Index', [
            'templates' => $templates,
        ]);
    }

    public function createFromTemplate(Request $request, FormTemplate $template)
    {
        // Validate request
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:'.FormTemplate::class,
            'redirect_url' => 'nullable|url'
        ]);

        // Create new form for user
        $userForm = $request->user()->formTemplates()->create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'redirect_url' => $validated['redirect_url'],
            'is_template' => false
        ]);

        // Copy all fields from template
        foreach ($template->fields as $field) {
            $userForm->fields()->create([
                'name' => $field->name,
                'type' => $field->type,
                'label' => $field->label,
                'is_required' => $field->is_required,
                'order' => $field->order,
                'properties' => $field->properties,
                'rendered_field' => $field->rendered_field
            ]);
        }

        return Inertia::location(route('form.builder', $userForm->id));
    }
    public function show(FormTemplate $template)
    {
        // add a check in this method to verify if this user is the owner of this template
        if(request()->user()->id !== $template->user_id) {
            abort(404, 'Page not found');
        }

        $template->load('fields');
        $products = Product::select('id', 'name')->get()->toArray();
        $fieldTypes = TemplateField::FIELD_TYPES;
        
        return Inertia::render('FormBuilder/Show', compact('template', 'fieldTypes', 'products'));
    }
    
    // public function storeField(Request $request, FormTemplate $template)
    // {
    //     if(request()->user()->id !== $template->user_id) {
    //         abort(403);
    //     }

    //     $validated = $request->validate([
    //         'name' => 'required|string',
    //         'type' => 'required|string',
    //         'label' => 'required|string',
    //         'is_required' => 'required|boolean',
    //         'order' => 'integer'
    //     ]);

    //     $field = $template->fields()->create([
    //         'name' => $validated['name'],
    //         'type' => $validated['type'],
    //         'label' => $validated['label'],
    //         'is_required' => $validated['is_required'],
    //         'order' => $validated['order'] ?? $template->fields()->count() + 1,
    //         'properties' => $this->getDefaultProperties($validated['type'])
    //     ]);

    //     return response()->json([
    //         'success' => true,
    //         'field' => $field,
    //         'rendered_field' => $this->renderField($field)
    //     ]);
    // }
    
    // public function getProperties(Request $request)
    // {
    //     $field = TemplateField::findOrFail($request->field_id);
        
    //     return response()->json([
    //         'html' => view('partials.field-properties', ['field' => $field])->render()
    //     ]);
    // }
    
    // public function updateField(Request $request, TemplateField $field)
    // {
        
    //     $validated = $request->validate([
    //         'label' => 'required|string',
    //         'is_required' => 'boolean',
    //         'properties' => 'nullable|array'
    //     ]);
    
    //     // Only update allowed fields - explicitly specify which fields to update
    //     $field->update([
    //         'label' => $validated['label'],
    //         'is_required' => $validated['is_required'] ?? false,
    //         'properties' => $validated['properties'] ?? []
    //     ]);
    
    //     return response()->json([
    //         'success' => true,
    //         'field' => $field->fresh()
    //     ]);

    // }
    // public function reorder(Request $request)
    // {
    //     $request->validate([
    //         'orders' => 'required|array',
    //         'orders.*.field_id' => 'required|exists:template_fields,id',
    //         'orders.*.order' => 'required|integer'
    //     ]);

    //     foreach ($request->orders as $order) {
    //         TemplateField::where('id', $order['field_id'])
    //             ->update(['order' => $order['order']]);
    //     }

    //     return response()->json(['success' => true]);
    // }

    public function storeField(Request $request, FormTemplate $template)
    {
        try {
            // Authorization check
            if ($request->user()->id !== $template->user_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized: You do not have permission to modify this template.'
                ], 403);
            }
    
            // Validation
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'type' => 'required|string', // Specify allowed types
                'label' => 'required|string|max:255',
                'is_required' => 'required|boolean',
                'order' => 'nullable|integer|min:0',
                'properties' => 'nullable|array' // Add properties validation
            ]);
    
            // Sanitize properties based on field type
            $properties = $this->sanitizeFieldProperties(
                $validated['type'], 
                $validated['properties'] ?? []
            );
    
            // Create field
            $field = $template->fields()->create([
                'name' => $validated['name'],
                'type' => $validated['type'],
                'label' => $validated['label'],
                'is_required' => $validated['is_required'],
                'order' => $validated['order'] ?? $template->fields()->count() + 1,
                'properties' => $properties
            ]);
    
            return response()->json([
                'success' => true,
                'message' => 'Field created successfully.',
                'field' => $field,
                'rendered_field' => $this->renderField($field)
            ], 201);
    
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed. ' . $e->errors()[0],
                'errors' => $e->errors()
            ], 422);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add field. ' . $e->getMessage(),
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
    
    /**
     * Sanitize field properties based on field type
     */
    protected function sanitizeFieldProperties(string $fieldType, array $properties): array
    {
        switch ($fieldType) {
            case 'select':
            case 'radio':
            case 'checkbox':
                // Ensure options are properly formatted
                if (isset($properties['options'])) {
                    $properties['options'] = $this->sanitizeOptions($properties['options']);
                }
                break;
                
            case 'product_selector':
                // Sanitize product data if needed
                if (isset($properties['products'])) {
                    $properties['products'] = array_map(function($product) {
                        return [
                            'product' => (string) ($product['product'] ?? ''),
                            'note' => (string) ($product['note'] ?? ''),
                            'price' => (float) ($product['price'] ?? 0),
                        ];
                    }, $properties['products']);
                }
                break;
        }
    
        return $properties;
    }
    
    /**
     * Sanitize options array to ensure proper format
     */
    protected function sanitizeOptions($options): array
    {
        // If options is not an array, make it one
        if (!is_array($options)) {
            return [(string) $options];
        }
    
        // Convert all options to strings and filter empty ones
        return array_values(array_filter(array_map(function($option) {
            if (is_array($option)) {
                // Handle arrays - either implode or take first value
                return implode(', ', array_filter($option, 'strlen'));
            }
            return (string) $option;
        }, $options)));
    }

    public function getProperties(Request $request)
    {
        try {
            $request->validate([
                'field_id' => 'required|exists:template_fields,id'
            ]);

            $field = TemplateField::findOrFail($request->field_id);
            
            return response()->json([
                'success' => true,
                'message' => 'Field properties retrieved successfully.',
                'html' => view('partials.field-properties', ['field' => $field])->render()
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid field ID provided.',
                'errors' => $e->errors()
            ], 422);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve field properties.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function updateField(Request $request, TemplateField $field)
    {
        try {

            // Authorization check (assuming field belongs to template which belongs to user)
            if ($request->user()->id !== $field->template->user_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized: You do not have permission to modify this field.'
                ], 403);
            }

            $validated = $request->validate([
                'label' => 'required|string|max:255',
                'is_required' => 'boolean',
                'properties' => 'nullable|array',
                'properties.*' => 'nullable' // Add specific property validation if needed
            ]);

            $field->update([
                'label' => $validated['label'],
                'is_required' => $validated['is_required'] ?? false,
                'properties' => $validated['properties'] ?? []
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Field updated successfully.',
                'field' => $field->fresh()
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors()
            ], 422);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update field. ' . $e->getMessage(),
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function reorder(Request $request)
    {
        try {
            $validated = $request->validate([
                'orders' => 'required|array',
                'orders.*.field_id' => 'required|exists:template_fields,id',
                'orders.*.order' => 'required|integer|min:0'
            ]);

            // Verify all fields belong to the same template and user
            $fieldIds = collect($validated['orders'])->pluck('field_id');
            $fields = TemplateField::whereIn('id', $fieldIds)->with('template')->get();
            
            $templateIds = $fields->pluck('template.id')->unique();
            if ($templateIds->count() > 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot reorder fields from different templates.'
                ], 400);
            }

            if ($request->user()->id !== $fields->first()->template->user_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized: You do not have permission to reorder these fields.'
                ], 403);
            }

            // Update orders in transaction
            DB::transaction(function () use ($validated) {
                foreach ($validated['orders'] as $order) {
                    TemplateField::where('id', $order['field_id'])
                        ->update(['order' => $order['order']]);
                }
            });

            return response()->json([
                'success' => true,
                'message' => 'Fields reordered successfully.'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid reorder data provided.',
                'errors' => $e->errors()
            ], 422);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reorder fields.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
    
    public function preview(FormTemplate $template)
    {
        try {
            // Eager load the fields relationship
            $template->load(['fields']);
            
            // Validate that all fields are properly filled
            $errors = [];
            
            foreach ($template->fields as $field) {
                // Check if this is a product field and validate its properties
                if ($field->type === 'products' || isset($field->properties['products'])) {
                    if (empty($field->properties['products'])) {
                        $errors[] = "The products field is empty.";
                        continue;
                    }
                    
                    foreach ($field->properties['products'] as $index => $product) {
                        if (empty($product['product'])) {
                            $errors[] = "Product #".($index+1)." is missing a product Selection, please select a product";
                        }
                        if (empty($product['note'])) {
                            $errors[] = "Product #".($index+1)." is missing a description/note";
                        }
                        if (empty($product['price'])) {
                            $errors[] = "Product #".($index+1)." is missing a price";
                        }
                        if (!isset($product['price']) || !is_numeric($product['price'])) {
                            $errors[] = "Product #".($index+1)." has an invalid price";
                        }
                    }
                }
                
                // Add validation for other field types here if needed
            }
            
            // If there are errors, return them
            if (!empty($errors)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please fix the following errors: ' . $errors[0],
                    'errors' => $errors
                ], 422); // 422 is Unprocessable Entity
            }
            
            // If validation passes, render the preview
            $html = view('form-builder.preview', ['template' => $template])->render();
            
            return response()->json([
                'success' => true,
                'html' => $html
            ]);
            
        } catch (\Exception $e) {
            // Handle any unexpected errors
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred while generating the preview.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    public function generateEmbed(FormTemplate $template)
    {
        $embedUrl = route('form.embed', $template->slug);
        
        $embedCode = <<<HTML
            <!-- Form Embed Code for {$template->name} -->
            <div id="embedded-form-{$template->slug}" class="embedded-form-container">
                <noscript>Please enable JavaScript to view this form.</noscript>
            </div>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    var container = document.getElementById('embedded-form-{$template->slug}');
                    if (container) {
                        var script = document.createElement('script');
                        script.src = '{$embedUrl}?v=' + new Date().getTime();;
                        script.async = true;
                        script.onerror = function() {
                            container.innerHTML = '<div class="form-error">Failed to load form. Please refresh or try again later.</div>';
                        };
                        container.appendChild(script);
                    }
                });
            </script>
        HTML;

        return response()->json([
            'success' => true,
            'embed_code' => trim(preg_replace('/\s+/', ' ', $embedCode)) // Minified
        ]);
    }
    
    public function saveForm(Request $request)
    {
        // $validated = $request->validate([
        //     'redirect_url' => 'nullable|string',
        //     'name' => 'required|string',
        // ]);

        // FormTemplate::updateOrCreate(
        //     [
        //         'user_id' => $request->user()->id,
        //     ],
        //     [
        //         'redirect_url' => $validated['redirect_url'],
        //         'name' => $validated['name']
        //     ]
        // );
        
        // return response()->json(['success' => true, 'message' => 'Form Template saved successfully!']);
    }
    
    protected function getDefaultProperties($type)
    {
        $properties = [];
        
        switch ($type) {
            case 'text':
                $properties = ['placeholder' => 'Enter text'];
                break;
            case 'number':
                $properties = ['min' => 0, 'max' => 100, 'step' => 1];
                break;
            case 'select':
            case 'radio':
                $properties = ['options' => [
                    ['value' => 'option1', 'label' => 'Option 1'],
                    ['value' => 'option2', 'label' => 'Option 2']
                ]];
                break;
            case 'product_selector':
                $properties = ['products' => [
                    ['product' => ''],
                    ['price' => ''],
                    ['note' => ''],
                ]];
                break;
            case 'textarea':
                $properties = ['rows' => 3];
                break;
        }
        
        return $properties;
    }

    public function destroyField(TemplateField $field)
    {
        $field->delete();

        return response()->json(['success' => true]);
    }

    private function renderField(TemplateField $field)
    {
        // Render field HTML based on type
        // Similar to your previous implementation
        return view('form-builder.fields.' . $field->type, ['field' => $field])->render();
    }
}
