(function() {
    var formData = {!! json_encode($form->form_data) !!};
    var formAction = "{{ route('form.submit', $form->slug) }}";
    
    var formHtml = `
        <form id="embeddedForm" action="${formAction}" method="POST">
            @csrf
            <div class="embedded-form">
                ${generateFields(formData.fields)}
                <button type="submit" class="btn btn-primary">Submit</button>
            </div>
        </form>
    `;
    
    document.getElementById('embedded-form-container').innerHTML = formHtml;
    
    function generateFields(fields) {
        var html = '';
        fields.forEach(function(field) {
            html += generateField(field);
        });
        return html;
    }
    
    function generateField(field) {
        // Implementation similar to the renderField method in TemplateField
        // but in JavaScript for client-side rendering
    }
    
    // Add form submission handling
    document.getElementById('embeddedForm').addEventListener('submit', function(e) {
        e.preventDefault();
        // Handle form submission via AJAX
    });
})();