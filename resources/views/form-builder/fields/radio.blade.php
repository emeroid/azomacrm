<div class="space-y-2 mt-2">
    @foreach($field->properties['options'] ?? [] as $option)
        <div class="flex items-center">
            <input type="radio" 
                   id="field_{{ $field->id }}_{{ $loop->index }}" 
                   name="{{ $field->name }}" 
                   value="{{ $option }}"
                   class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300"
                   @if(old($field->name) == $option) checked @endif>
            <label for="field_{{ $field->id }}_{{ $loop->index }}" class="ml-2 block text-sm text-gray-700">
                {{ $option }}
            </label>
        </div>
    @endforeach
</div>