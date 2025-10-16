function loadForm(config) {
    fetch(`/forms/${config.formId}`)
        .then(response => response.json())
        .then(form => {
            const container = document.querySelector(config.container);
            if (!container) return;
            
            const formElement = document.createElement('form');
            formElement.method = 'POST';
            formElement.action = config.submitUrl;
            formElement.enctype = form.has_file ? 'multipart/form-data' : 'application/x-www-form-urlencoded';
            formElement.className = 'space-y-4';
            
            // Add CSRF token if using Laravel
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            if (csrfToken) {
                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = csrfToken;
                formElement.appendChild(csrfInput);
            }
            
            // Add form description if exists
            if (form.description) {
                const description = document.createElement('p');
                description.className = 'text-gray-600';
                description.textContent = form.description;
                formElement.appendChild(description);
            }
            
            // Add form fields
            form.fields.forEach(field => {
                const fieldContainer = document.createElement('div');
                fieldContainer.className = 'space-y-1';
                
                const label = document.createElement('label');
                label.className = 'block text-sm font-medium text-gray-700';
                label.textContent = field.label;
                if (field.is_required) {
                    const requiredSpan = document.createElement('span');
                    requiredSpan.className = 'text-red-500';
                    requiredSpan.textContent = ' *';
                    label.appendChild(requiredSpan);
                }
                fieldContainer.appendChild(label);
                
                let inputElement;
                
                switch (field.type) {
                    case 'text':
                    case 'email':
                    case 'date':
                        inputElement = document.createElement('input');
                        inputElement.type = field.type;
                        inputElement.name = field.name;
                        inputElement.className = 'mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm';
                        if (field.default_value) inputElement.value = field.default_value;
                        if (field.is_required) inputElement.required = true;
                        break;
                        
                    case 'textarea':
                        inputElement = document.createElement('textarea');
                        inputElement.name = field.name;
                        inputElement.className = 'mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm';
                        inputElement.rows = 3;
                        if (field.default_value) inputElement.textContent = field.default_value;
                        if (field.is_required) inputElement.required = true;
                        break;
                        
                    case 'select':
                        inputElement = document.createElement('select');
                        inputElement.name = field.name;
                        inputElement.className = 'mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm';
                        if (field.is_required) inputElement.required = true;
                        
                        field.options.forEach(option => {
                            const optionElement = document.createElement('option');
                            optionElement.value = option.value;
                            optionElement.textContent = option.label;
                            if (field.default_value === option.value) optionElement.selected = true;
                            inputElement.appendChild(optionElement);
                        });
                        break;
                        
                    case 'radio':
                    case 'checkbox':
                        const optionsContainer = document.createElement('div');
                        optionsContainer.className = 'space-y-2';
                        
                        field.options.forEach((option, index) => {
                            const optionContainer = document.createElement('div');
                            optionContainer.className = 'flex items-center';
                            
                            const optionInput = document.createElement('input');
                            optionInput.type = field.type;
                            optionInput.name = field.type === 'radio' ? field.name : `${field.name}[]`;
                            optionInput.value = option.value;
                            optionInput.id = `${field.name}-${index}`;
                            optionInput.className = 'h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded';
                            if (field.default_value === option.value) optionInput.checked = true;
                            
                            const optionLabel = document.createElement('label');
                            optionLabel.htmlFor = `${field.name}-${index}`;
                            optionLabel.className = 'ml-2 block text-sm text-gray-700';
                            optionLabel.textContent = option.label;
                            
                            optionContainer.appendChild(optionInput);
                            optionContainer.appendChild(optionLabel);
                            optionsContainer.appendChild(optionContainer);
                        });
                        
                        fieldContainer.appendChild(optionsContainer);
                        break;
                        
                    case 'file':
                        inputElement = document.createElement('input');
                        inputElement.type = 'file';
                        inputElement.name = field.name;
                        inputElement.className = 'mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100';
                        if (field.is_required) inputElement.required = true;
                        break;
                }
                
                if (inputElement) fieldContainer.appendChild(inputElement);
                
                if (field.validation_rules) {
                    const validationNote = document.createElement('p');
                    validationNote.className = 'text-xs text-gray-500 mt-1';
                    validationNote.textContent = `Validation: ${field.validation_rules}`;
                    fieldContainer.appendChild(validationNote);
                }
                
                formElement.appendChild(fieldContainer);
            });
            
            // Add submit button
            const submitButton = document.createElement('button');
            submitButton.type = 'submit';
            submitButton.className = 'inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500';
            submitButton.textContent = 'Submit';
            formElement.appendChild(submitButton);
            
            // Handle form submission
            formElement.addEventListener('submit', async (e) => {
                e.preventDefault();
                
                const formData = new FormData(formElement);
                const submitButton = formElement.querySelector('button[type="submit"]');
                const originalButtonText = submitButton.textContent;
                
                submitButton.disabled = true;
                submitButton.textContent = 'Submitting...';
                
                try {
                    const response = await fetch(config.submitUrl, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'Accept': 'application/json',
                        },
                    });
                    
                    const data = await response.json();
                    
                    if (response.ok) {
                        if (form.redirect_url) {
                            window.location.href = form.redirect_url;
                        } else {
                            container.innerHTML = `
                                <div class="bg-green-50 border border-green-200 rounded-md p-4">
                                    <div class="flex">
                                        <div class="flex-shrink-0">
                                            <svg class="h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                        <div class="ml-3">
                                            <p class="text-sm font-medium text-green-800">
                                                ${data.message || 'Form submitted successfully!'}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            `;
                        }
                    } else {
                        throw new Error(data.message || 'Failed to submit form');
                    }
                } catch (error) {
                    container.insertAdjacentHTML('afterbegin', `
                        <div class="bg-red-50 border border-red-200 rounded-md p-4 mb-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-red-800">
                                        ${error.message}
                                    </p>
                                </div>
                            </div>
                        </div>
                    `);
                } finally {
                    submitButton.disabled = false;
                    submitButton.textContent = originalButtonText;
                }
            });
            
            container.appendChild(formElement);
        })
        .catch(error => {
            console.error('Error loading form:', error);
            const container = document.querySelector(config.container);
            if (container) {
                container.innerHTML = `
                    <div class="bg-red-50 border border-red-200 rounded-md p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-red-800">
                                    Failed to load form. Please try again later.
                                </p>
                            </div>
                        </div>
                    </div>
                `;
            }
        });
}