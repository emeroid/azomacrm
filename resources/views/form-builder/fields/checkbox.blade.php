<div class="flex items-center mt-2">
    <input type="checkbox" 
           name="{{ $field->name }}"
           id="field_{{ $field->id }}"
           value="1"
           class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
           @if(old($field->name)) checked @endif>
    <label for="field_{{ $field->id }}" class="ml-2 block text-sm text-gray-700">
        {{ $field->label }}
    </label>
</div>