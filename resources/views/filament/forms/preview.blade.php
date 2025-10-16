<div class="space-y-4 p-6 bg-white rounded-lg shadow">
    @if($formData['description'] ?? false)
        <p class="text-gray-600">{{ $formData['description'] }}</p>
    @endif
    
    @foreach($fields as $field)
        <div class="space-y-1">
            <label class="block text-sm font-medium text-gray-700">
                {{ $field['label'] }}
                @if($field['is_required'])
                    <span class="text-red-500">*</span>
                @endif
            </label>
            
            @if($field['type'] === 'text')
                <input type="text" name="{{ $field['name'] }}" 
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                       @if($field['is_required']) required @endif
                       @if($field['default_value']) value="{{ $field['default_value'] }}" @endif>
            
            @elseif($field['type'] === 'email')
                <input type="email" name="{{ $field['name'] }}" 
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                       @if($field['is_required']) required @endif
                       @if($field['default_value']) value="{{ $field['default_value'] }}" @endif>
            
            @elseif($field['type'] === 'textarea')
                <textarea name="{{ $field['name'] }}" rows="3"
                          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                          @if($field['is_required']) required @endif>{{ $field['default_value'] ?? '' }}</textarea>
            
            @elseif($field['type'] === 'select')
                <select name="{{ $field['name'] }}" 
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        @if($field['is_required']) required @endif>
                    @foreach($field['options'] ?? [] as $option)
                        <option value="{{ $option['value'] }}" @if($field['default_value'] === $option['value']) selected @endif>
                            {{ $option['label'] }}
                        </option>
                    @endforeach
                </select>
            
            @elseif(in_array($field['type'], ['checkbox', 'radio']))
                <div class="space-y-2">
                    @foreach($field['options'] ?? [] as $option)
                        <div class="flex items-center">
                            <input type="{{ $field['type'] }}" name="{{ $field['name'] }}" 
                                   id="{{ $field['name'] }}-{{ $loop->index }}" 
                                   value="{{ $option['value'] }}"
                                   class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                                   @if($field['default_value'] === $option['value']) checked @endif>
                            <label for="{{ $field['name'] }}-{{ $loop->index }}" class="ml-2 block text-sm text-gray-700">
                                {{ $option['label'] }}
                            </label>
                        </div>
                    @endforeach
                </div>
            
            @elseif($field['type'] === 'file')
                <input type="file" name="{{ $field['name'] }}" 
                       class="mt-1 block w-full text-sm text-gray-500
                              file:mr-4 file:py-2 file:px-4
                              file:rounded-md file:border-0
                              file:text-sm file:font-semibold
                              file:bg-indigo-50 file:text-indigo-700
                              hover:file:bg-indigo-100"
                       @if($field['is_required']) required @endif>
            
            @elseif($field['type'] === 'date')
                <input type="date" name="{{ $field['name'] }}" 
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                       @if($field['is_required']) required @endif
                       @if($field['default_value']) value="{{ $field['default_value'] }}" @endif>
            @endif
            
            @if($field['validation_rules'])
                <p class="text-xs text-gray-500 mt-1">Validation: {{ $field['validation_rules'] }}</p>
            @endif
        </div>
    @endforeach
    
    <div class="pt-4">
        <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            Submit
        </button>
    </div>
</div>