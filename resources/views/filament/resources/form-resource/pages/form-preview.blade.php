// resources/views/filament/forms/components/form-preview.blade.php

<div class="p-4 bg-gray-50 rounded-lg">
    <h3 class="text-lg font-medium mb-4">Form Preview</h3>
    
    @foreach([] as $field)
        <div class="mb-4" data-field-id="{{ $field->id }}" data-field-name="{{ $field->name }}">
            <label class="block text-sm font-medium text-gray-700">{{ $field->label }}</label>
            
            @if($field->type === 'text')
                <input type="text" 
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500" 
                       @if($field->is_required) required @endif>
            
            @elseif($field->type === 'textarea')
                <textarea class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500" 
                          rows="3"
                          @if($field->is_required) required @endif></textarea>
            
            @elseif($field->type === 'email')
                <input type="email" 
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500" 
                       @if($field->is_required) required @endif>
            
            @elseif($field->type === 'number')
                <input type="number" 
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500" 
                       @if($field->is_required) required @endif>
            
            @elseif($field->type === 'select')
                <select class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500" 
                        @if($field->is_required) required @endif>
                    <option value="">Select an option</option>
                    @if($field->options)
                        @foreach($field->options as $option)
                            <option value="{{ $option }}">{{ $option }}</option>
                        @endforeach
                    @endif
                </select>
            
            @elseif($field->type === 'checkbox')
                <div class="mt-2">
                    @if($field->options)
                        @foreach($field->options as $option)
                            <div class="flex items-center">
                                <input type="checkbox" 
                                       id="{{ $field->name }}_{{ $loop->index }}" 
                                       class="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                <label for="{{ $field->name }}_{{ $loop->index }}" class="ml-2 block text-sm text-gray-700">{{ $option }}</label>
                            </div>
                        @endforeach
                    @else
                        <div class="flex items-center">
                            <input type="checkbox" 
                                   id="{{ $field->name }}" 
                                   class="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                            <label for="{{ $field->name }}" class="ml-2 block text-sm text-gray-700">{{ $field->label }}</label>
                        </div>
                    @endif
                </div>
            
            @elseif($field->type === 'radio')
                <div class="mt-2">
                    @foreach($field->options as $option)
                        <div class="flex items-center">
                            <input type="radio" 
                                   id="{{ $field->name }}_{{ $loop->index }}" 
                                   name="{{ $field->name }}" 
                                   class="rounded-full border-gray-300 text-primary-600 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                            <label for="{{ $field->name }}_{{ $loop->index }}" class="ml-2 block text-sm text-gray-700">{{ $option }}</label>
                        </div>
                    @endforeach
                </div>
            
            @elseif($field->type === 'date')
                <input type="date" 
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500" 
                       @if($field->is_required) required @endif>
            
            @elseif($field->type === 'file')
                <input type="file" 
                       class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100" 
                       @if($field->is_required) required @endif>
            @endif
            
            @if($field->validation_rules)
                <p class="mt-1 text-sm text-gray-500">Validation: {{ $field->validation_rules }}</p>
            @endif
        </div>
    @endforeach
    
    <button type="button" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
        Submit Form
    </button>
</div>

@push('scripts')
<script>
document.addEventListener('livewire:init', () => {
    Livewire.on('fieldUpdated', (fieldId, data) => {
        const fieldContainer = document.querySelector(`[data-field-id="${fieldId}"]`);
        if (!fieldContainer) return;
        
        // Update label
        if (data.label) {
            const label = fieldContainer.querySelector('label');
            if (label) label.textContent = data.label;
        }
        
        // Update required attribute
        if (data.is_required !== undefined) {
            const inputs = fieldContainer.querySelectorAll('input, select, textarea');
            inputs.forEach(input => {
                if (data.is_required) {
                    input.setAttribute('required', 'required');
                } else {
                    input.removeAttribute('required');
                }
            });
        }
        
        // Update options for select/radio/checkbox
        if (data.options && Array.isArray(data.options)) {
            const select = fieldContainer.querySelector('select');
            if (select) {
                select.innerHTML = '<option value="">Select an option</option>';
                data.options.forEach(option => {
                    select.innerHTML += `<option value="${option}">${option}</option>`;
                });
            }
            
            // Update radio buttons
            const radioContainer = fieldContainer.querySelector('div[class*="mt-2"]');
            if (radioContainer && data.type === 'radio') {
                radioContainer.innerHTML = '';
                data.options.forEach((option, index) => {
                    radioContainer.innerHTML += `
                        <div class="flex items-center">
                            <input type="radio" 
                                   id="${data.name}_${index}" 
                                   name="${data.name}" 
                                   class="rounded-full border-gray-300 text-primary-600 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                            <label for="${data.name}_${index}" class="ml-2 block text-sm text-gray-700">${option}</label>
                        </div>
                    `;
                });
            }
        }
    });
});
</script>
@endpush