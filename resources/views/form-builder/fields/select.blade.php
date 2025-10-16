<select name="{{ $field->name }}" 
    id="field_{{ $field->id }}" 
    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
    @if($field->is_required) required @endif>
<option value="">Select an option</option>
@foreach($field->properties['options'] ?? [] as $option)
    <option value="{{ $option }}" @if(old($field->name) == $option) selected @endif>
        {{ $option }}
    </option>
@endforeach
</select>