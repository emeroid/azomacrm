@php
    // Get data from state during creation or from record during edit
    $primaryColor = $getState('primary_color') ?? $getRecord()?->primary_color ?? '#3b82f6';
    $fields = $getState('fields') ?? $getRecord()?->fields ?? [];

@endphp

<div class="p-4 border rounded-lg bg-white shadow-sm">
    <h3 class="font-bold text-lg mb-4" style="color: {{ $primaryColor }};">
          Preview
        <span class="text-xs font-normal text-gray-500">(Live)</span>
    </h3>
    
    <div class="space-y-4">
        @foreach($fields as $field)
            @php
                $field = is_array($field) ? $field : $field->toArray();
            @endphp
            
            <div>
                <label class="block text-sm font-medium text-gray-700" for="preview-{{ $field['name'] ?? 'field' }}">
                    {{ $field['label'] ?? 'Field Label' }}
                    @if($field['required'] ?? false)
                        <span class="text-red-500">*</span>
                    @endif
                </label>
                
                @if(in_array($field['type'] ?? 'text', ['text', 'email', 'number']))
                    <input type="{{ $field['type'] ?? 'text' }}" 
                           id="preview-{{ $field['name'] ?? 'field' }}"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                           placeholder="{{ $field['placeholder'] ?? '' }}"
                           @if($field['required'] ?? false) required @endif>
                           
                @elseif(($field['type'] ?? 'text') === 'textarea')
                    <textarea id="preview-{{ $field['name'] ?? 'field' }}"
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                              placeholder="{{ $field['placeholder'] ?? '' }}"
                              @if($field['required'] ?? false) required @endif></textarea>
                              
                @elseif(($field['type'] ?? 'text') === 'select')
                    <select id="preview-{{ $field['name'] ?? 'field' }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            @if($field['required'] ?? false) required @endif>
                        <option value="">Select an option</option>
                        @isset($field['options'])
                            @foreach(explode(',', $field['options']) as $option)
                                @if(trim($option) !== '')
                                    <option value="{{ trim($option) }}">{{ trim($option) }}</option>
                                @endif
                            @endforeach
                        @else
                            <option value="option1">Option 1</option>
                            <option value="option2">Option 2</option>
                        @endisset
                    </select>
                    
                @elseif(in_array(($field['type'] ?? 'text'), ['checkbox', 'radio']))
                    <div class="mt-2 space-y-2">
                        @isset($field['options'])
                            @foreach(array_filter(explode(',', $field['options'])) as $option)
                                <div class="flex items-center">
                                    <input type="{{ $field['type'] ?? 'radio' }}" 
                                           id="preview-{{ $field['name'] ?? 'field' }}-{{ $loop->index }}"
                                           name="preview-{{ $field['name'] ?? 'field' }}"
                                           value="{{ trim($option) }}"
                                           class="h-4 w-4 border-gray-300"
                                           style="color: {{ $primaryColor }};"
                                           @if($field['type'] === 'checkbox') 
                                               name="preview-{{ $field['name'] ?? 'field' }}[]"
                                           @endif>
                                    <label for="preview-{{ $field['name'] ?? 'field' }}-{{ $loop->index }}" class="ml-2 block text-sm text-gray-700">
                                        {{ trim($option) }}
                                    </label>
                                </div>
                            @endforeach
                        @else
                            <div class="flex items-center">
                                <input type="{{ $field['type'] ?? 'radio' }}" 
                                       id="preview-{{ $field['name'] ?? 'field' }}-1"
                                       name="preview-{{ $field['name'] ?? 'field' }}"
                                       class="h-4 w-4 border-gray-300"
                                       style="color: {{ $primaryColor }};">
                                <label for="preview-{{ $field['name'] ?? 'field' }}-1" class="ml-2 block text-sm text-gray-700">
                                    Option 1
                                </label>
                            </div>
                        @endisset
                    </div>
                @endif
            </div>
        @endforeach
        
        @empty($fields)
            <div class="text-center text-gray-500 py-8">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <p class="mt-2">Add fields to see the preview</p>
            </div>
        @endempty
        
        @if(!empty($fields))
            <button type="button" 
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white focus:outline-none focus:ring-2 focus:ring-offset-2 transition-colors"
                    style="background-color: {{ $primaryColor }};">
                Submit Form
            </button>
        @endif
    </div>
</div>