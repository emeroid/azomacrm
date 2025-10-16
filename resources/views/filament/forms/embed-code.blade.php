<div class="bg-gray-100 p-4 rounded-lg">
    <h3 class="text-lg font-medium mb-2">Embed Code</h3>
    <p class="text-sm text-gray-600 mb-4">Copy and paste this code into your website to display the form.</p>
    
    <div class="relative">
        <pre class="bg-gray-800 text-gray-100 p-4 rounded overflow-x-auto text-sm"><code>&lt;div id="form-container-{{ $form->id }}"&gt;&lt;/div&gt;
&lt;script src="{{ url('/forms/embed.js') }}"&gt;&lt;/script&gt;
&lt;script&gt;
    loadForm({
        formId: {{ $form->id }},
        container: '#form-container-{{ $form->id }}',
        submitUrl: '{{ route('forms.submit', $form->slug) }}'
    });
&lt;/script&gt;</code></pre>
        
        <button onclick="navigator.clipboard.writeText(this.previousElementSibling.textContent)" 
                class="absolute top-2 right-2 bg-gray-700 hover:bg-gray-600 text-white p-1 rounded">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path d="M8 3a1 1 0 011-1h2a1 1 0 110 2H9a1 1 0 01-1-1z" />
                <path d="M6 3a2 2 0 00-2 2v11a2 2 0 002 2h8a2 2 0 002-2V5a2 2 0 00-2-2 3 3 0 01-3 3H9a3 3 0 01-3-3z" />
            </svg>
        </button>
    </div>
    
    <div class="mt-4">
        <h4 class="font-medium mb-2">Preview:</h4>
        <div class="border border-gray-300 p-4 rounded">
            @include('forms.preview', [
                'fields' => $form->fields,
                'formData' => $form
            ])
        </div>
    </div>
</div>