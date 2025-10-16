<div class="max-w-4xl mx-auto p-6">
    <h2 class="text-2xl font-semibold mb-6 text-gray-800">{{ $template->name }} Preview</h2>
    
    <form class="space-y-6">
        @foreach($template->fields as $field)
            <div class="space-y-1">
                <label class="block text-sm font-medium text-gray-700">
                    {{ $field->label }}
                    @if($field->is_required)
                        <span class="text-red-500">*</span>
                    @endif
                </label>
                
                @include('form-builder.fields.' . $field->type, ['field' => $field])
                
                @error($field->name)
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        @endforeach

        <div class="pt-4">
            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                Submit Form
            </button>
        </div>
    </form>
</div>