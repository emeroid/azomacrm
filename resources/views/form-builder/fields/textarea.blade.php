<textarea name="{{ $field->name }}" 
    id="field_{{ $field->id }}" 
    rows="{{ $field->properties['rows'] ?? 3 }}" 
    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
    @if($field->is_required) required @endif>{{ old($field->name) }}</textarea>