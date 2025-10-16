
(function() {

    // style render
    // Unique ID for the container to scope all CSS rules
    const containerId = 'form-container-{{ $template->slug }}';

    // 1. Create and inject the CSS styles
    const styleElement = document.createElement('style');
    styleElement.textContent = `
        #${containerId} * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        #${containerId} {
            background: white;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            width: 100%;
            max-width: 800px;
            overflow: hidden;
            border: 1px solid #e2e8f0; /* Optional: Add border for clarity on host page */
        }

        #${containerId} .form-header {
            background: #4caf50;
            color: white;
            padding: 25px 30px;
            text-align: center;
            position: relative;
        }

        #${containerId} .form-header h1 {
            font-size: 28px;
            margin-bottom: 5px;
            font-weight: 700;
        }

        #${containerId} .countdown {
            background: rgba(0, 0, 0, 0.15);
            display: inline-block;
            padding: 10px 20px;
            border-radius: 30px;
            margin-top: 15px;
            font-size: 24px;
            font-weight: 600;
            letter-spacing: 1px;
        }

        #${containerId} .countdown-label {
            font-size: 14px;
            margin-top: 8px;
            opacity: 0.9;
        }

        #${containerId} .form-body {
            padding: 30px;
        }

        #${containerId} .section-title {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #4caf50;
            color: #2d3748;
        }

        #${containerId} .form-group {
            margin-bottom: 20px;
        }

        #${containerId} .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #2d3748;
        }

        #${containerId} .form-control {
            width: 100%;
            padding: 14px;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            background: white;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        #${containerId} .form-control:focus {
            outline: none;
            border-color: #4caf50;
            box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.2);
        }

        #${containerId} select.form-control {
            padding: 14px 12px;
        }

        #${containerId} textarea.form-control {
            min-height: 100px;
            resize: vertical;
        }

        #${containerId} .package-options {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }

        #${containerId} .package-option {
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            padding: 15px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
        }

        #${containerId} .package-option:hover {
            border-color: #4caf50;
            background: #f0f9f0;
        }

        #${containerId} .package-option.selected {
            border-color: #4caf50;
            background: #f0f9f0;
            box-shadow: 0 5px 15px rgba(76, 175, 80, 0.2);
        }

        #${containerId} .package-name {
            font-weight: 600;
            margin-bottom: 5px;
        }

        #${containerId} .package-price {
            font-weight: 700;
            color: #4caf50;
            font-size: 18px;
        }

        #${containerId} .order-summary {
            background: #f9fbf9;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.03);
            margin-top: 20px;
            border: 1px solid #e2f0e3;
        }

        #${containerId} .summary-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e2e8f0;
        }

        #${containerId} .summary-total {
            display: flex;
            justify-content: space-between;
            font-size: 20px;
            font-weight: 700;
            color: #2d3748;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 2px solid #e2e8f0;
        }

        #${containerId} .btn-submit {
            width: 100%;
            padding: 18px;
            border: none;
            border-radius: 8px;
            background: #4caf50;
            color: white;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 30px;
            box-shadow: 0 5px 15px rgba(76, 175, 80, 0.3);
        }

        #${containerId} .btn-submit:hover {
            background: #43a047;
            transform: translateY(-2px);
            box-shadow: 0 7px 20px rgba(76, 175, 80, 0.4);
        }

        #${containerId} .btn-submit:disabled {
            background: #a5d6a7;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        #${containerId} .secure-notice {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
            color: #718096;
        }

        #${containerId} .secure-notice i {
            color: #4caf50;
            margin-right: 5px;
        }

        #${containerId} .form-alert-message {
            padding: 1rem;
            margin-bottom: 1rem;
            font-size: 0.875rem;
            border-radius: 0.375rem;
        }

        #${containerId} .error-message {
            margin-top: 0.25rem;
            font-size: 0.875rem;
            color: #dc2626;
        }

        @media (max-width: 768px) {
            #${containerId} .package-options {
                grid-template-columns: 1fr;
            }
                 
            #${containerId} .form-header {
                padding: 20px 15px;
            }
                 
            #${containerId} .form-body {
                padding: 20px 15px;
            }
        }
    `;

    // Simple auto-save functionality
    class SimpleFormTracker {
        constructor(templateSlug) {
            this.templateSlug = templateSlug;
            this.saveTimeout = null;
            this.isSubmitting = false;
            this.init();
        }

        init() {
            this.setupAutoSave();
            this.setupUnloadHandler();
        }

        // Helper for debouncing function calls
        debounce(func, wait) {
            let timeout;
            return function executed(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }

        setupAutoSave() {
            const form = document.getElementById('order-form');
            if (!form) return;

            // Use a single, debounced function call for all changes
            const debouncedSave = this.debounce(this.saveDraft.bind(this), 800);

            // Save on input (for text fields)
            form.addEventListener('input', (e) => {
                if (this.isInputElement(e.target)) {
                    debouncedSave();
                }
            });

            // Save immediately on change (for selects, radios, checkboxes, etc.)
            form.addEventListener('change', (e) => {
                if (this.isInputElement(e.target)) {
                    this.saveDraft();
                }
            });
        }

        isInputElement(element) {
            return ['INPUT', 'SELECT', 'TEXTAREA'].includes(element.tagName) && 
                element.name && 
                element.type !== 'submit' && 
                element.type !== 'button';
        }

        // ---------------------------------------------------------------------
        // THE FIX: Sends all form data instead of just the one changed field
        // ---------------------------------------------------------------------
        async saveDraft() {
            const form = document.getElementById('order-form');
            if (!form || this.isSubmitting) return;

            try {
                const formData = new FormData(form); // Captures ALL fields

                // Quick check to prevent sending completely empty drafts
                let hasData = false;
                for (const [key, value] of formData.entries()) {
                    if (value && String(value).trim() !== '') {
                        hasData = true;
                        break;
                    }
                }
                if (!hasData) return;

                const response = await fetch('{{ route('form.save-draft', $template->slug) }}', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: formData
                });

                if (response.ok) {
                    const data = await response.json();
                    console.log(`Draft saved. Fields: ${JSON.stringify(data)}`);
                }
            } catch (error) {
                console.error('Failed to save draft:', error);
            }
        }

        setupUnloadHandler() {
            window.addEventListener('beforeunload', async (e) => {
                if (this.isSubmitting) return;

                // Check if form has any data
                const form = document.getElementById('order-form');
                if (!form) return;

                const formData = new FormData(form);
                let hasData = false;

                for (let [key, value] of formData.entries()) {
                    if (value.trim() !== '') {
                        hasData = true;
                        break;
                    }
                }

                if (hasData) {
                    // Use sendBeacon for reliable unload sending
                    const formData = new FormData();
                    navigator.sendBeacon('{{ route('form.mark-abandoned', $template->slug) }}', formData);
                }
            });
        }

        markAsSubmitting() {
            this.isSubmitting = true;
        }
    }
    // Append the style element to the document's <head>
    document.head.appendChild(styleElement);

    // 2. Create the HTML elements dynamically
    const formContainer = document.createElement('div');
    formContainer.id = containerId;
    formContainer.className = 'form-container';

    const formHeader = document.createElement('div');
    formHeader.className = 'form-header';
    formHeader.innerHTML = `
        <h1>Complete Your Order</h1>
        <div class="countdown" id="countdown">02:31</div>
        <div class="countdown-label">Time Remaining</div>
    `;
    formContainer.appendChild(formHeader);

    const formBody = document.createElement('div');
    formBody.className = 'form-body';
    
    // Create form element
    const form = document.createElement('form');
    form.id = 'order-form';
    form.method = 'POST';
    // form.action = '{{ route('form.submit', $template->slug) }}';

    // Add initial titles
    const mainTitle = document.createElement('h2');
    mainTitle.className = 'section-title';
    mainTitle.textContent = 'Fill in your details below to place your order';
    form.appendChild(mainTitle);

    const personalInfoTitle = document.createElement('h3');
    personalInfoTitle.className = 'section-title';
    personalInfoTitle.textContent = 'Personal Information';
    form.appendChild(personalInfoTitle);
    
    // Add form fields based on the old script's logic
    @foreach($template->fields as $field)
        const fieldWrapper{{ $field->id }} = document.createElement('div');
        fieldWrapper{{ $field->id }}.className = 'form-group';
        
        const label{{ $field->id }} = document.createElement('label');
        label{{ $field->id }}.htmlFor = 'field_{{ $field->id }}';
        label{{ $field->id }}.textContent = '{{ $field->label }}';
        @if($field->is_required)
            requiredSpan = document.createElement('span');
            requiredSpan.style.color = '#ef4444';
            requiredSpan.textContent = '*';
            label{{ $field->id }}.appendChild(requiredSpan);
        @endif
        fieldWrapper{{ $field->id }}.appendChild(label{{ $field->id }});
        
        @if($field->type === 'text' || $field->type === 'email' || $field->type === 'tel' || $field->type === 'number')
            const input{{ $field->id }} = document.createElement('input');
            input{{ $field->id }}.type = '{{ $field->type }}';
            input{{ $field->id }}.id = 'field_{{ $field->id }}';
            input{{ $field->id }}.name = '{{ $field->name }}';
            input{{ $field->id }}.className = 'form-control';
            @if($field->is_required)
                input{{ $field->id }}.required = true;
            @endif
            @if(isset($field->properties['placeholder']))
                input{{ $field->id }}.placeholder = '{{ $field->properties['placeholder'] }}';
            @endif
            fieldWrapper{{ $field->id }}.appendChild(input{{ $field->id }});
        
        @elseif($field->type === 'textarea')
            const textarea{{ $field->id }} = document.createElement('textarea');
            textarea{{ $field->id }}.id = 'field_{{ $field->id }}';
            textarea{{ $field->id }}.name = '{{ $field->name }}';
            textarea{{ $field->id }}.className = 'form-control';
            @if($field->is_required)
                textarea{{ $field->id }}.required = true;
            @endif
            @if(isset($field->properties['placeholder']))
                textarea{{ $field->id }}.placeholder = '{{ $field->properties['placeholder'] }}';
            @endif
            @if(isset($field->properties['rows']))
                textarea{{ $field->id }}.rows = {{ $field->properties['rows'] }};
            @endif
            fieldWrapper{{ $field->id }}.appendChild(textarea{{ $field->id }});
        
        @elseif($field->type === 'select')
            const select{{ $field->id }} = document.createElement('select');
            select{{ $field->id }}.id = 'field_{{ $field->id }}';
            select{{ $field->id }}.name = '{{ $field->name }}';
            select{{ $field->id }}.className = 'form-control';
            @if($field->is_required)
                select{{ $field->id }}.required = true;
            @endif
            
            const defaultOption = document.createElement('option');
            defaultOption.value = '';
            defaultOption.textContent = '{{ $field->properties['placeholder'] ?? 'Select Option' }}';
            select{{ $field->id }}.appendChild(defaultOption);
            
            @if(isset($field->properties['options']))
                @foreach($field->properties['options'] as $option)
                    const option{{ $field->id }}_{{ $loop->index }} = document.createElement('option');
                    option{{ $field->id }}_{{ $loop->index }}.value = '{{ trim($option) }}';
                    option{{ $field->id }}_{{ $loop->index }}.textContent = '{{ trim($option) }}';
                    select{{ $field->id }}.appendChild(option{{ $field->id }}_{{ $loop->index }});
                @endforeach
            @endif
            fieldWrapper{{ $field->id }}.appendChild(select{{ $field->id }});
        
        @elseif($field->type === 'radio' || $field->type === 'checkbox')
            // This section is for dynamic radio/checkboxes. Your old script used basic
            // styling. To match the new form, we will use the package-options style.
            const packageOptions = document.createElement('div');
            packageOptions.className = 'package-options';
            
            @if(isset($field->properties['options']))
                @foreach(explode(',', $field->properties['options']) as $option)
                    const optionWrapper{{ $field->id }}_{{ $loop->index }} = document.createElement('div');
                    optionWrapper{{ $field->id }}_{{ $loop->index }}.className = 'package-option';
                    
                    const optionText = document.createElement('div');
                    optionText.className = 'package-name';
                    optionText.textContent = '{{ trim($option) }}';

                    const input{{ $field->id }}_{{ $loop->index }} = document.createElement('input');
                    input{{ $field->id }}_{{ $loop->index }}.type = '{{ $field->type }}';
                    input{{ $field->id }}_{{ $loop->index }}.id = 'field_{{ $field->id }}_{{ $loop->index }}';
                    input{{ $field->id }}_{{ $loop->index }}.name = '{{ $field->name }}';
                    input{{ $field->id }}_{{ $loop->index }}.value = '{{ trim($option) }}';
                    input{{ $field->id }}_{{ $loop->index }}.style.display = 'none'; // Hide the actual radio button
                    
                    optionWrapper{{ $field->id }}_{{ $loop->index }}.appendChild(input{{ $field->id }}_{{ $loop->index }});
                    optionWrapper{{ $field->id }}_{{ $loop->index }}.appendChild(optionText);
                    
                    optionWrapper{{ $field->id }}_{{ $loop->index }}.addEventListener('click', function() {
                        document.querySelectorAll('.package-option').forEach(opt => opt.classList.remove('selected'));
                        this.classList.add('selected');
                        input{{ $field->id }}_{{ $loop->index }}.checked = true;
                    });
                    
                    packageOptions.appendChild(optionWrapper{{ $field->id }}_{{ $loop->index }});
                @endforeach
            @endif
            fieldWrapper{{ $field->id }}.appendChild(packageOptions);
        
        @elseif($field->type === 'date')
            const dateInput{{ $field->id }} = document.createElement('input');
            dateInput{{ $field->id }}.type = 'date';
            dateInput{{ $field->id }}.id = 'field_{{ $field->id }}';
            dateInput{{ $field->id }}.name = '{{ $field->name }}';
            dateInput{{ $field->id }}.className = 'form-control';
            @if($field->is_required)
                dateInput{{ $field->id }}.required = true;
            @endif
            fieldWrapper{{ $field->id }}.appendChild(dateInput{{ $field->id }});
        
        @elseif($field->type === 'product_selector')
            const productSelector{{ $field->id }} = document.createElement('div');
            productSelector{{ $field->id }}.className = 'package-options';
            
            @if(isset($field->properties['products']))
            @foreach($field->properties['products'] as $index => $product)
                const productWrapper{{ $field->id }}_{{ $index }} = document.createElement('div');
                productWrapper{{ $field->id }}_{{ $index }}.className = 'package-option';
                productWrapper{{ $field->id }}_{{ $index }}.setAttribute('data-price', '{{ number_format($product['price'] ?? 0, 0, '', '') }}');
        
                // Use a unique variable name for each iteration
                const productName_{{ $index }} = document.createElement('div');
                productName_{{ $index }}.className = 'package-name';
                productName_{{ $index }}.textContent = '{{ $product['note'] ?? '' }}';
        
                // Use a unique variable name for each iteration
                const productPrice_{{ $index }} = document.createElement('div');
                productPrice_{{ $index }}.className = 'package-price';
                productPrice_{{ $index }}.textContent = `N{{ number_format($product['price'] ?? 0, 0) }}`;
        
                const radioInput{{ $field->id }}_{{ $index }} = document.createElement('input');
                radioInput{{ $field->id }}_{{ $index }}.type = 'radio';
                radioInput{{ $field->id }}_{{ $index }}.name = 'products';
                radioInput{{ $field->id }}_{{ $index }}.style.display = 'none';
                radioInput{{ $field->id }}_{{ $index }}.value = '{{ $product['product'] }}::{{ number_format($product['price'] ?? 0, 2, '.', '') }}::{{ $product['note'] }}';
                radioInput{{ $field->id }}_{{ $index }}.required = true;
        
                productWrapper{{ $field->id }}_{{ $index }}.appendChild(radioInput{{ $field->id }}_{{ $index }});
                productWrapper{{ $field->id }}_{{ $index }}.appendChild(productName_{{ $index }}); // Append the unique variable
                productWrapper{{ $field->id }}_{{ $index }}.appendChild(productPrice_{{ $index }}); // Append the unique variable
                
                productWrapper{{ $field->id }}_{{ $index }}.addEventListener('click', function() {
                    document.querySelectorAll('.package-option').forEach(opt => opt.classList.remove('selected'));
                    this.classList.add('selected');
                    radioInput{{ $field->id }}_{{ $index }}.checked = true;
                    const price = this.getAttribute('data-price');
                    updateSummary(price);
                });

                productSelector{{ $field->id }}.appendChild(productWrapper{{ $field->id }}_{{ $index }});
            @endforeach
            @endif
            fieldWrapper{{ $field->id }}.appendChild(productSelector{{ $field->id }});
        @endif
        form.appendChild(fieldWrapper{{ $field->id }});
    @endforeach

    // Add order summary section
    const orderSummary = document.createElement('div');
    orderSummary.className = 'order-summary';
    orderSummary.innerHTML = `
        <div class="summary-item">
            <div>Subtotal:</div>
            <div id="summary-subtotal"></div>
        </div>
        <div class="summary-item">
            <div>Shipping:</div>
            <div>Free</div>
        </div>
        <div class="summary-item">
            <div>Tax:</div>
            <div>Included</div>
        </div>
        <div class="summary-total">
            <div>Order Total:</div>
            <div id="total-amount"></div>
        </div>
    `;
    form.appendChild(orderSummary);
    
    // Add submit button
    const submitButton = document.createElement('button');
    submitButton.type = 'submit';
    submitButton.textContent = 'Complete My Order';
    submitButton.className = 'btn-submit';
    form.appendChild(submitButton);

    // Add secure notice
    const secureNotice = document.createElement('div');
    secureNotice.className = 'secure-notice';
    secureNotice.innerHTML = `<i class="fas fa-lock"></i> Your information is secure and encrypted`;
    form.appendChild(secureNotice);
    
    // Append the entire form to the body
    formBody.appendChild(form);
    formContainer.appendChild(formBody);
    
    // Insert into target element
    const targetElement = document.currentScript.parentElement;
    targetElement.insertBefore(formContainer, document.currentScript);

    // Initialize the simple tracker
    const formTracker = new SimpleFormTracker('{{ $template->slug }}');
    // === END OF SIMPLE FORM TRACKER ===

    // Initial total amount update (for the first selected option if any)
    function updateSummary(price) {
        const totalAmountElement = document.getElementById('total-amount');
        const subtotalElement = document.getElementById('summary-subtotal');
        const formattedPrice = 'N' + parseInt(price).toLocaleString('en-US');
        if (totalAmountElement) {
            totalAmountElement.textContent = formattedPrice;
        }
        if (subtotalElement) {
            subtotalElement.textContent = formattedPrice;
        }
    }
    
    const initialSelected = document.querySelector('.package-option.selected');
    if (initialSelected) {
        updateSummary(initialSelected.getAttribute('data-price'));
    }

    // New countdown and package selection logic (from the new form)
    let minutes = 10;
    let seconds = 31;
    
    function updateCountdown() {
        const countdownElement = document.getElementById('countdown');
        if (!countdownElement) return;

        const formattedMinutes = minutes.toString().padStart(2, '0');
        const formattedSeconds = seconds.toString().padStart(2, '0');
        
        countdownElement.textContent = `${formattedMinutes}:${formattedSeconds}`;
        
        if (seconds === 0) {
            if (minutes === 0) {
                return;
            }
            minutes--;
            seconds = 59;
        } else {
            seconds--;
        }
        setTimeout(updateCountdown, 1000);
    }
    
    updateCountdown();

    const packageOptions = document.querySelectorAll('.package-option');
    packageOptions.forEach(option => {
        option.addEventListener('click', function() {
            packageOptions.forEach(opt => opt.classList.remove('selected'));
            this.classList.add('selected');
            const price = this.getAttribute('data-price');
            updateSummary(price);
        });
    });

    
    // Form submission logic from the old script (updated with new class names)
    if (form) {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();

            const submitButton = this.querySelector('.btn-submit');
            const originalButtonText = submitButton.textContent;
            
            // Mark as submitting to prevent abandonment tracking
            if (formTracker) {
                formTracker.markAsSubmitting();
            }

            // Start Loading State
            submitButton.disabled = true;
            submitButton.textContent = 'Submitting...';

            // Clear previous errors and messages
            document.querySelectorAll('.error-message').forEach(el => el.remove());
            const existingMessageDiv = formContainer.querySelector('.form-alert-message');
            if (existingMessageDiv) {
                existingMessageDiv.remove();
            }

            try {
                const formData = new FormData(form);
                const response = await fetch('{{ route('form.submit', $template->slug) }}', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: formData
                });

                const data = await response.json();

                if (response.ok) {
                    if (data.redirect_url) {
                        window.location.href = data.redirect_url;
                    } else {
                        const successDiv = document.createElement('div');
                        successDiv.className = 'form-alert-message';
                        successDiv.style.color = '#047857';
                        successDiv.style.backgroundColor = '#d1fae5';
                        successDiv.textContent = data.message || 'Order submitted successfully!';
                        
                        form.remove();
                        formBody.prepend(successDiv);
                    }
                } else {
                    const errorDiv = document.createElement('div');
                    errorDiv.className = 'form-alert-message';
                    errorDiv.style.color = '#dc2626';
                    errorDiv.style.backgroundColor = '#fee2e2';

                    if (response.status === 422 && data.errors) {
                        errorDiv.textContent = 'Please correct the errors below.';
                        form.prepend(errorDiv);
                        Object.entries(data.errors).forEach(([field, messages]) => {
                            let fieldElement = form.querySelector(`[name="${field}"]`);
                            if (!fieldElement && field.includes('.')) {
                                const parts = field.split('.');
                                fieldElement = form.querySelector(`[name$="[${parts[parts.length - 1]}]"]`);
                            }

                            if (fieldElement) {
                                const errorElement = document.createElement('p');
                                errorElement.className = 'error-message';
                                errorElement.textContent = messages.join(', ');
                                
                                const parent = fieldElement.closest('.form-group');
                                if (parent) {
                                    parent.appendChild(errorElement);
                                } else {
                                    fieldElement.after(errorElement);
                                }
                                fieldElement.style.borderColor = '#dc2626';
                                fieldElement.addEventListener('input', function() {
                                    this.style.borderColor = '#e2e8f0';
                                    const specificError = form.querySelector(`.error-message[data-field="${field}"]`);
                                    if(specificError) specificError.remove();
                                }, { once: true });
                            }
                        });
                    } else if (data.message) {
                        errorDiv.textContent = data.message;
                        form.prepend(errorDiv);
                    } else {
                        errorDiv.textContent = `An unexpected error occurred.`;
                        form.prepend(errorDiv);
                    }
                }
            } catch (error) {
                console.error('Fetch Error:', error);
                const errorDiv = document.createElement('div');
                errorDiv.className = 'form-alert-message';
                errorDiv.style.color = '#dc2626';
                errorDiv.style.backgroundColor = '#fee2e2';
                errorDiv.textContent = 'A network error occurred. Please check your connection and try again.';
                formBody.innerHTML = '';
                formBody.appendChild(errorDiv);
            } finally {
                // End Loading State
                submitButton.disabled = false;
                submitButton.textContent = originalButtonText;
            }
        });
    }

    // Load Font Awesome for the lock icon if not already present
    if (!document.querySelector('script[src*="font-awesome"]')) {
        const faScript = document.createElement('script');
        faScript.src = 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js';
        faScript.crossOrigin = 'anonymous';
        document.head.appendChild(faScript);
    }
})();


{{-- (function() {
    // Create form container
    const formContainer = document.createElement('div');
    formContainer.style.maxWidth = '70%';
    formContainer.style.margin = '0 auto';
    formContainer.style.padding = '1rem';
    formContainer.style.fontFamily = 'sans-serif';
    
    // Create form element
    const form = document.createElement('form');
    form.method = 'POST';
    // form.action = '{{ route('form.submit', $template->slug) }}';
    form.style.display = 'grid';
    form.style.gap = '1.5rem';
    
    // Add form fields
    @foreach($template->fields as $field)
        const fieldWrapper{{ $field->id }} = document.createElement('div');
        fieldWrapper{{ $field->id }}.style.display = 'grid';
        fieldWrapper{{ $field->id }}.style.gap = '0.5rem';
        
        const label{{ $field->id }} = document.createElement('label');
        label{{ $field->id }}.htmlFor = 'field_{{ $field->id }}';
        label{{ $field->id }}.style.display = 'block';
        label{{ $field->id }}.style.fontSize = '0.875rem';
        label{{ $field->id }}.style.fontWeight = '500';
        label{{ $field->id }}.style.color = '#374151';
        label{{ $field->id }}.textContent = '{{ $field->label }}';
        @if($field->is_required)
            requiredSpan = document.createElement('span');
            requiredSpan.style.color = '#ef4444';
            requiredSpan.textContent = '*';
            label{{ $field->id }}.appendChild(requiredSpan);
        @endif
        fieldWrapper{{ $field->id }}.appendChild(label{{ $field->id }});
        
        @if($field->type === 'text' || $field->type === 'email' || $field->type === 'tel' || $field->type === 'number')
            const input{{ $field->id }} = document.createElement('input');
            input{{ $field->id }}.type = '{{ $field->type }}';
            input{{ $field->id }}.id = 'field_{{ $field->id }}';
            input{{ $field->id }}.name = '{{ $field->name }}';
            input{{ $field->id }}.style.marginTop = '0.25rem';
            input{{ $field->id }}.style.display = 'block';
            input{{ $field->id }}.style.width = '100%';
            input{{ $field->id }}.style.borderRadius = '0.375rem';
            input{{ $field->id }}.style.border = '1px solid #d1d5db';
            input{{ $field->id }}.style.padding = '0.5rem';
            input{{ $field->id }}.style.boxShadow = '0 1px 2px 0 rgba(0, 0, 0, 0.05)';
            input{{ $field->id }}.style.outline = 'none';
            input{{ $field->id }}.style.fontSize = '0.875rem';
            input{{ $field->id }}.style.lineHeight = '1.25rem';
            input{{ $field->id }}.addEventListener('focus', function() {
                this.style.borderColor = '#6366f1';
                this.style.boxShadow = '0 0 0 2px rgba(99, 102, 241, 0.5)';
            });
            input{{ $field->id }}.addEventListener('blur', function() {
                this.style.borderColor = '#d1d5db';
                this.style.boxShadow = '0 1px 2px 0 rgba(0, 0, 0, 0.05)';
            });
            @if($field->is_required)
                input{{ $field->id }}.required = true;
            @endif
            @if(isset($field->properties['placeholder']))
                input{{ $field->id }}.placeholder = '{{ $field->properties['placeholder'] }}';
            @endif
            fieldWrapper{{ $field->id }}.appendChild(input{{ $field->id }});
        
        @elseif($field->type === 'textarea')
            const textarea{{ $field->id }} = document.createElement('textarea');
            textarea{{ $field->id }}.id = 'field_{{ $field->id }}';
            textarea{{ $field->id }}.name = '{{ $field->name }}';
            textarea{{ $field->id }}.style.marginTop = '0.25rem';
            textarea{{ $field->id }}.style.display = 'block';
            textarea{{ $field->id }}.style.width = '100%';
            textarea{{ $field->id }}.style.borderRadius = '0.375rem';
            textarea{{ $field->id }}.style.border = '1px solid #d1d5db';
            textarea{{ $field->id }}.style.padding = '0.5rem';
            textarea{{ $field->id }}.style.boxShadow = '0 1px 2px 0 rgba(0, 0, 0, 0.05)';
            textarea{{ $field->id }}.style.outline = 'none';
            textarea{{ $field->id }}.style.fontSize = '0.875rem';
            textarea{{ $field->id }}.style.lineHeight = '1.25rem';
            textarea{{ $field->id }}.addEventListener('focus', function() {
                this.style.borderColor = '#6366f1';
                this.style.boxShadow = '0 0 0 2px rgba(99, 102, 241, 0.5)';
            });
            textarea{{ $field->id }}.addEventListener('blur', function() {
                this.style.borderColor = '#d1d5db';
                this.style.boxShadow = '0 1px 2px 0 rgba(0, 0, 0, 0.05)';
            });
            @if($field->is_required)
                textarea{{ $field->id }}.required = true;
            @endif
            @if(isset($field->properties['placeholder']))
                textarea{{ $field->id }}.placeholder = '{{ $field->properties['placeholder'] }}';
            @endif
            @if(isset($field->properties['rows']))
                textarea{{ $field->id }}.rows = {{ $field->properties['rows'] }};
            @endif
            fieldWrapper{{ $field->id }}.appendChild(textarea{{ $field->id }});
        
        @elseif($field->type === 'select')
            const select{{ $field->id }} = document.createElement('select');
            select{{ $field->id }}.id = 'field_{{ $field->id }}';
            select{{ $field->id }}.name = '{{ $field->name }}';
            select{{ $field->id }}.style.marginTop = '0.25rem';
            select{{ $field->id }}.style.display = 'block';
            select{{ $field->id }}.style.width = '100%';
            select{{ $field->id }}.style.borderRadius = '0.375rem';
            select{{ $field->id }}.style.border = '1px solid #d1d5db';
            select{{ $field->id }}.style.padding = '0.5rem 0.75rem';
            select{{ $field->id }}.style.fontSize = '1rem';
            select{{ $field->id }}.style.outline = 'none';
            select{{ $field->id }}.addEventListener('focus', function() {
                this.style.borderColor = '#6366f1';
                this.style.boxShadow = '0 0 0 2px rgba(99, 102, 241, 0.5)';
            });
            select{{ $field->id }}.addEventListener('blur', function() {
                this.style.borderColor = '#d1d5db';
                this.style.boxShadow = 'none';
            });
            @if($field->is_required)
                select{{ $field->id }}.required = true;
            @endif
            
            @if(isset($field->properties['placeholder']))
                const defaultOption = document.createElement('option');
                defaultOption.value = '';
                defaultOption.textContent = '{{ $field->properties['placeholder'] }}';
                select{{ $field->id }}.appendChild(defaultOption);
            @endif
            
            @if(isset($field->properties['options']))
                @foreach($field->properties['options'] as $index => $option)
                    const option{{ $field->id }}_{{ $index }} = document.createElement('option');
                    option{{ $field->id }}_{{ $index }}.value = '{{ trim($option) }}';
                    option{{ $field->id }}_{{ $index }}.textContent = '{{ trim($option) }}';
                    select{{ $field->id }}.appendChild(option{{ $field->id }}_{{ $index }});
                @endforeach
            @endif
            fieldWrapper{{ $field->id }}.appendChild(select{{ $field->id }});
        
        @elseif($field->type === 'radio' || $field->type === 'checkbox')
            @if(isset($field->properties['options']))
                const optionsContainer{{ $field->id }} = document.createElement('div');
                optionsContainer{{ $field->id }}.style.display = 'flex';
                optionsContainer{{ $field->id }}.style.flexDirection = 'column';
                optionsContainer{{ $field->id }}.style.gap = '0.5rem';
                optionsContainer{{ $field->id }}.style.marginTop = '0.25rem';
                
                @foreach(explode(',', $field->properties['options']) as $option)
                    const optionWrapper{{ $field->id }}_{{ $loop->index }} = document.createElement('div');
                    optionWrapper{{ $field->id }}_{{ $loop->index }}.style.display = 'flex';
                    optionWrapper{{ $field->id }}_{{ $loop->index }}.style.alignItems = 'center';
                    
                    const input{{ $field->id }}_{{ $loop->index }} = document.createElement('input');
                    input{{ $field->id }}_{{ $loop->index }}.type = '{{ $field->type }}';
                    input{{ $field->id }}_{{ $loop->index }}.id = 'field_{{ $field->id }}_{{ $loop->index }}';
                    input{{ $field->id }}_{{ $loop->index }}.name = '{{ $field->type === 'radio' ? $field->name : $field->name.'[]' }}';
                    input{{ $field->id }}_{{ $loop->index }}.value = '{{ trim($option) }}';
                    input{{ $field->id }}_{{ $loop->index }}.style.height = '1rem';
                    input{{ $field->id }}_{{ $loop->index }}.style.width = '1rem';
                    input{{ $field->id }}_{{ $loop->index }}.style.borderColor = '#d1d5db';
                    input{{ $field->id }}_{{ $loop->index }}.style.color = '#6366f1';
                    input{{ $field->id }}_{{ $loop->index }}.addEventListener('focus', function() {
                        this.style.boxShadow = '0 0 0 2px rgba(99, 102, 241, 0.5)';
                    });
                    input{{ $field->id }}_{{ $loop->index }}.addEventListener('blur', function() {
                        this.style.boxShadow = 'none';
                    });
                    
                    const label{{ $field->id }}_{{ $loop->index }} = document.createElement('label');
                    label{{ $field->id }}_{{ $loop->index }}.htmlFor = 'field_{{ $field->id }}_{{ $loop->index }}';
                    label{{ $field->id }}_{{ $loop->index }}.style.marginLeft = '0.5rem';
                    label{{ $field->id }}_{{ $loop->index }}.style.display = 'block';
                    label{{ $field->id }}_{{ $loop->index }}.style.fontSize = '0.875rem';
                    label{{ $field->id }}_{{ $loop->index }}.style.color = '#374151';
                    label{{ $field->id }}_{{ $loop->index }}.textContent = '{{ trim($option) }}';
                    
                    optionWrapper{{ $field->id }}_{{ $loop->index }}.appendChild(input{{ $field->id }}_{{ $loop->index }});
                    optionWrapper{{ $field->id }}_{{ $loop->index }}.appendChild(label{{ $field->id }}_{{ $loop->index }});
                    optionsContainer{{ $field->id }}.appendChild(optionWrapper{{ $field->id }}_{{ $loop->index }});
                @endforeach
                fieldWrapper{{ $field->id }}.appendChild(optionsContainer{{ $field->id }});
            @endif
        
        @elseif($field->type === 'date')
            const dateInput{{ $field->id }} = document.createElement('input');
            dateInput{{ $field->id }}.type = 'date';
            dateInput{{ $field->id }}.id = 'field_{{ $field->id }}';
            dateInput{{ $field->id }}.name = '{{ $field->name }}';
            dateInput{{ $field->id }}.style.marginTop = '0.25rem';
            dateInput{{ $field->id }}.style.display = 'block';
            dateInput{{ $field->id }}.style.width = '100%';
            dateInput{{ $field->id }}.style.borderRadius = '0.375rem';
            dateInput{{ $field->id }}.style.border = '1px solid #d1d5db';
            dateInput{{ $field->id }}.style.padding = '0.5rem';
            dateInput{{ $field->id }}.style.boxShadow = '0 1px 2px 0 rgba(0, 0, 0, 0.05)';
            dateInput{{ $field->id }}.style.fontSize = '0.875rem';
            dateInput{{ $field->id }}.addEventListener('focus', function() {
                this.style.borderColor = '#6366f1';
                this.style.boxShadow = '0 0 0 2px rgba(99, 102, 241, 0.5)';
            });
            dateInput{{ $field->id }}.addEventListener('blur', function() {
                this.style.borderColor = '#d1d5db';
                this.style.boxShadow = '0 1px 2px 0 rgba(0, 0, 0, 0.05)';
            });
            @if($field->is_required)
                dateInput{{ $field->id }}.required = true;
            @endif
            fieldWrapper{{ $field->id }}.appendChild(dateInput{{ $field->id }});
        
        @elseif($field->type === 'product_selector')
            const productSelector{{ $field->id }} = document.createElement('div');
            productSelector{{ $field->id }}.style.display = 'flex';
            productSelector{{ $field->id }}.style.flexDirection = 'column';
            productSelector{{ $field->id }}.style.gap = '1rem';
            
            @if(isset($field->properties['products']))
            @foreach($field->properties['products'] as $index => $product)
                const productWrapper{{ $field->id }}_{{ $index }} = document.createElement('div');
                productWrapper{{ $field->id }}_{{ $index }}.style.display = 'flex';
                productWrapper{{ $field->id }}_{{ $index }}.style.alignItems = 'center';
                productWrapper{{ $field->id }}_{{ $index }}.style.justifyContent = 'space-between';
                productWrapper{{ $field->id }}_{{ $index }}.style.padding = '0.75rem';
                productWrapper{{ $field->id }}_{{ $index }}.style.border = '1px solid #e5e7eb';
                productWrapper{{ $field->id }}_{{ $index }}.style.borderRadius = '0.375rem';
        
                const productInner{{ $field->id }}_{{ $index }} = document.createElement('div');
                productInner{{ $field->id }}_{{ $index }}.style.display = 'flex';
                productInner{{ $field->id }}_{{ $index }}.style.alignItems = 'center';
                productInner{{ $field->id }}_{{ $index }}.style.gap = '1rem';
        
                // Radio input
                const radioInput{{ $field->id }}_{{ $index }} = document.createElement('input');
                radioInput{{ $field->id }}_{{ $index }}.type = 'radio';
                radioInput{{ $field->id }}_{{ $index }}.name = 'products'; // We'll use a single name for the combined value
                radioInput{{ $field->id }}_{{ $index }}.id = 'product_{{ $field->id }}_{{ $product['product'] }}';
        
                // *** CRUCIAL CHANGE: Combine product_id and price in the value attribute ***
                // We'll use a delimiter like '::' or '|' to separate them.
                // Example: "product_id::price"
                radioInput{{ $field->id }}_{{ $index }}.value = '{{ $product['product'] }}::{{ number_format($product['price'] ?? 0, 2, '.', '') }}::{{ $product['note'] }}';
        
                radioInput{{ $field->id }}_{{ $index }}.style.height = '1rem';
                radioInput{{ $field->id }}_{{ $index }}.style.width = '1rem';
                radioInput{{ $field->id }}_{{ $index }}.style.color = '#6366f1';
                radioInput{{ $field->id }}_{{ $index }}.style.borderColor = '#d1d5db';
                radioInput{{ $field->id }}_{{ $index }}.required = true;
        
                // Product label with note and price
                const productLabel{{ $field->id }}_{{ $index }} = document.createElement('label');
                productLabel{{ $field->id }}_{{ $index }}.htmlFor = 'product_{{ $field->id }}_{{ $product['product'] }}';
                productLabel{{ $field->id }}_{{ $index }}.style.display = 'block';
                productLabel{{ $field->id }}_{{ $index }}.style.fontSize = '0.875rem';
                productLabel{{ $field->id }}_{{ $index }}.style.fontWeight = '500';
                productLabel{{ $field->id }}_{{ $index }}.style.color = '#374151';
        
                // Get product details - Inline the Blade values directly
                productLabel{{ $field->id }}_{{ $index }}.textContent =
                    `{{ $product['note'] ?? '' ? ($product['note'] ?? '') . ' - ' : '' }}NGN{{ number_format($product['price'] ?? 0, 2) }}`;
        
                // Append elements
                productInner{{ $field->id }}_{{ $index }}.appendChild(radioInput{{ $field->id }}_{{ $index }});
                productInner{{ $field->id }}_{{ $index }}.appendChild(productLabel{{ $field->id }}_{{ $index }});
                productWrapper{{ $field->id }}_{{ $index }}.appendChild(productInner{{ $field->id }}_{{ $index }});
                productSelector{{ $field->id }}.appendChild(productWrapper{{ $field->id }}_{{ $index }});
            @endforeach
        @endif
            fieldWrapper{{ $field->id }}.appendChild(productSelector{{ $field->id }});
        @endif
        form.appendChild(fieldWrapper{{ $field->id }});
    @endforeach
    
    // Add submit button
    const submitButton = document.createElement('button');
    submitButton.type = 'submit';
    submitButton.textContent = 'Submit';
    submitButton.style.padding = '0.5rem 1rem';
    submitButton.style.backgroundColor = '#2563eb';
    submitButton.style.color = 'white';
    submitButton.style.borderRadius = '0.375rem';
    submitButton.style.border = 'none';
    submitButton.style.cursor = 'pointer';
    submitButton.style.fontSize = '0.875rem';
    submitButton.style.lineHeight = '1.25rem';
    submitButton.style.fontWeight = '500';
    submitButton.addEventListener('mouseenter', () => {
        submitButton.style.backgroundColor = '#1d4ed8';
    });
    submitButton.addEventListener('mouseleave', () => {
        submitButton.style.backgroundColor = '#2563eb';
    });
    submitButton.addEventListener('focus', () => {
        submitButton.style.outline = 'none';
        submitButton.style.boxShadow = '0 0 0 2px rgba(99, 102, 241, 0.5)';
    });
    submitButton.addEventListener('blur', () => {
        submitButton.style.boxShadow = 'none';
    });
    form.appendChild(submitButton);
    
    // Add form to container
    formContainer.appendChild(form);
    
    // Insert into target element
    const targetElement = document.currentScript.parentElement;
    targetElement.insertBefore(formContainer, document.currentScript);
    
    if (form) {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();

            // Find the submit button within the form
            // You might need to adjust this selector based on your HTML structure
            const submitButton = this.querySelector('button[type="submit"]');
            const originalButtonText = submitButton ? submitButton.textContent : 'Submit';

            // --- Start Loading State ---
            if (submitButton) {
                submitButton.disabled = true; // Disable the button to prevent multiple submissions
                submitButton.textContent = 'Submitting...'; // Change text to indicate loading
                // Optionally, add a loading spinner icon here if you have one
                // e.g., submitButton.innerHTML = '<span class="spinner"></span> Submitting...';
                // You might also want to apply a visual style for disabled state (e.g., opacity)
                // submitButton.style.opacity = '0.7';
                // submitButton.style.cursor = 'not-allowed';
            }
            // --- End Loading State Setup ---

            // Clear previous errors and general messages
            document.querySelectorAll('.error-message').forEach(el => el.remove());
            const existingMessageDiv = formContainer.querySelector('.form-alert-message');
            if (existingMessageDiv) {
                existingMessageDiv.remove();
            }

            try {
                const formData = new FormData(form);
                const response = await fetch('{{ route('form.submit', $template->slug) }}', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: formData
                });

                const data = await response.json();

                if (response.ok) {
                    if (data.redirect_url) {
                        window.location.href = data.redirect_url;
                    } else {
                        const successDiv = document.createElement('div');
                        successDiv.className = 'form-alert-message';
                        successDiv.style.padding = '1rem';
                        successDiv.style.marginBottom = '1rem';
                        successDiv.style.fontSize = '0.875rem';
                        successDiv.style.color = '#047857';
                        successDiv.style.backgroundColor = '#d1fae5';
                        successDiv.style.borderRadius = '0.375rem';
                        successDiv.textContent = data.message || 'Form submitted successfully!';
                        
                        formContainer.innerHTML = ''; // Clears form, replace with message
                        formContainer.appendChild(successDiv);
                    }
                } else {
                    const errorDiv = document.createElement('div');
                    errorDiv.className = 'form-alert-message';
                    errorDiv.style.padding = '1rem';
                    errorDiv.style.marginBottom = '1rem';
                    errorDiv.style.fontSize = '0.875rem';
                    errorDiv.style.color = '#dc2626';
                    errorDiv.style.backgroundColor = '#fee2e2';
                    errorDiv.style.borderRadius = '0.375rem';

                    if (response.status === 422 && data.errors) {
                        errorDiv.textContent = 'Please correct the errors below.';
                        form.prepend(errorDiv);
                        Object.entries(data.errors).forEach(([field, messages]) => {
                            let fieldElement = form.querySelector(`[name="${field}"]`);
                            if (!fieldElement && field.includes('.')) {
                                const parts = field.split('.');
                                fieldElement = form.querySelector(`[name$="[${parts[parts.length - 1]}]"]`);
                            }

                            if (fieldElement) {
                                const errorElement = document.createElement('p');
                                errorElement.className = 'error-message';
                                errorElement.style.marginTop = '0.25rem';
                                errorElement.style.fontSize = '0.875rem';
                                errorElement.style.color = '#dc2626';
                                errorElement.textContent = messages.join(', ');

                                const parent = fieldElement.type === 'radio' || fieldElement.type === 'checkbox'
                                    ? fieldElement.closest('.product-wrapper') || fieldElement.parentNode
                                    : fieldElement;

                                if (parent) {
                                    const existingFieldError = parent.querySelector(`.error-message[data-field="${field}"]`);
                                    if (!existingFieldError) {
                                        errorElement.setAttribute('data-field', field);
                                        parent.after(errorElement);
                                    }
                                }

                                fieldElement.style.borderColor = '#dc2626';
                                fieldElement.addEventListener('input', function() {
                                    this.style.borderColor = '#d1d5db';
                                    const specificError = form.querySelector(`.error-message[data-field="${field}"]`);
                                    if(specificError) specificError.remove();
                                    if (this.type === 'radio') {
                                        form.querySelectorAll(`[name="${this.name}"]`).forEach(radio => {
                                            radio.style.borderColor = '#d1d5db';
                                        });
                                        const radioError = form.querySelector(`.error-message[data-field="${field}"]`);
                                        if (radioError) radioError.remove();
                                    }
                                }, { once: true });
                            } else {
                                errorDiv.textContent += `\n${messages.join(', ')}`;
                            }
                        });
                    } else if (data.message) {
                        errorDiv.textContent = data.message;
                        form.prepend(errorDiv);
                    } else {
                        errorDiv.textContent = `An unexpected error occurred (Status: ${response.status}).`;
                        form.prepend(errorDiv);
                    }
                }
            } catch (error) {
                console.error('Fetch Error:', error);
                const errorDiv = document.createElement('div');
                errorDiv.className = 'form-alert-message';
                errorDiv.style.padding = '1rem';
                errorDiv.style.marginBottom = '1rem';
                errorDiv.style.fontSize = '0.875rem';
                errorDiv.style.color = '#dc2626';
                errorDiv.style.backgroundColor = '#fee2e2';
                errorDiv.style.borderRadius = '0.375rem';
                errorDiv.textContent = 'A network error occurred. Please check your connection and try again.';
                formContainer.innerHTML = '';
                formContainer.appendChild(errorDiv);
            } finally {
                // --- End Loading State Teardown (always execute) ---
                if (submitButton) {
                    submitButton.disabled = false; // Re-enable the button
                    submitButton.textContent = originalButtonText; // Restore original text
                    // submitButton.style.opacity = ''; // Reset opacity
                    // submitButton.style.cursor = ''; // Reset cursor
                }
                // --- End Loading State Teardown ---
            }
        });
    } else {
        console.warn("Form element not found. Please ensure 'form' variable is correctly initialized.");
    } --}}
    {{-- form.addEventListener('submit', async function(e) {
        e.preventDefault();
    
        // Clear previous errors
        document.querySelectorAll('.error-message').forEach(el => el.remove());
        // Also clear any previous general error/success messages
        const existingMessageDiv = formContainer.querySelector('.form-alert-message');
        if (existingMessageDiv) {
            existingMessageDiv.remove();
        }
    
    
        try {
            const formData = new FormData(form);
            const response = await fetch('{{ route('form.submit', $template->slug) }}', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: formData
            });
    
            const data = await response.json(); // Always attempt to parse JSON
    
            if (response.ok) {
                if (data.redirect_url) {
                    // Redirect if redirect_url is present
                    window.location.href = data.redirect_url;
                } else {
                    
                    // Success response (status 2xx)
                    const successDiv = document.createElement('div');
                    successDiv.className = 'form-alert-message'; // Add a class for easy targeting
                    successDiv.style.padding = '1rem';
                    successDiv.style.marginBottom = '1rem';
                    successDiv.style.fontSize = '0.875rem';
                    successDiv.style.color = '#047857';
                    successDiv.style.backgroundColor = '#d1fae5';
                    successDiv.style.borderRadius = '0.375rem';
                    successDiv.textContent = data.message || 'Form submitted successfully!';
                    formContainer.innerHTML = ''; // Clears form, replace with message
                    formContainer.appendChild(successDiv);
                }
            } else {
                // Error response (status 4xx or 5xx)
                const errorDiv = document.createElement('div');
                errorDiv.className = 'form-alert-message'; // Add a class for easy targeting
                errorDiv.style.padding = '1rem';
                errorDiv.style.marginBottom = '1rem';
                errorDiv.style.fontSize = '0.875rem';
                errorDiv.style.color = '#dc2626';
                errorDiv.style.backgroundColor = '#fee2e2';
                errorDiv.style.borderRadius = '0.375rem';
    
                // Handle validation errors (Laravel's default 422 Unprocessable Entity)
                if (response.status === 422 && data.errors) {
                    errorDiv.textContent = 'Please correct the errors below.'; // Generic message for validation
                    form.prepend(errorDiv); // Display general error message above the form
                    // Loop through field-specific errors
                    Object.entries(data.errors).forEach(([field, messages]) => {
                        // Try to find by name, then by id if name isn't suitable for lookup (e.g., radio buttons)
                        let fieldElement = form.querySelector(`[name="${field}"]`);
                        if (!fieldElement && field.includes('.')) { // For nested arrays like 'products.0.product_id'
                            const parts = field.split('.');
                            // This is a simplified attempt. For complex nested names, you might need a more robust selector
                            // or pass specific data attributes from Blade to JS.
                            fieldElement = form.querySelector(`[name$="[${parts[parts.length - 1]}]"]`);
                        }
    
    
                        if (fieldElement) {
                            const errorElement = document.createElement('p');
                            errorElement.className = 'error-message'; // For field-specific errors
                            errorElement.style.marginTop = '0.25rem';
                            errorElement.style.fontSize = '0.875rem';
                            errorElement.style.color = '#dc2626';
                            errorElement.textContent = messages.join(', ');
    
                            // Append error message relative to the input field
                            // If it's a radio/checkbox, append to its parent container
                            const parent = fieldElement.type === 'radio' || fieldElement.type === 'checkbox'
                                ? fieldElement.closest('.product-wrapper') || fieldElement.parentNode // Assuming .product-wrapper for radios
                                : fieldElement;
    
                            if (parent) {
                                 // Find if an error message for this field already exists to prevent duplicates
                                const existingFieldError = parent.querySelector(`.error-message[data-field="${field}"]`);
                                if (!existingFieldError) {
                                    errorElement.setAttribute('data-field', field); // Mark error for this field
                                    parent.after(errorElement); // Place error after the field or its parent
                                }
                            }
    
                            // Highlight the problematic field
                            fieldElement.style.borderColor = '#dc2626';
                            // Reset border color on input (once)
                            fieldElement.addEventListener('input', function() {
                                this.style.borderColor = '#d1d5db';
                                // Also remove the specific error message when input is changed
                                const specificError = form.querySelector(`.error-message[data-field="${field}"]`);
                                if(specificError) specificError.remove();
                                // If it's a radio, clicking any in the group should clear its group error
                                if (this.type === 'radio') {
                                    form.querySelectorAll(`[name="${this.name}"]`).forEach(radio => {
                                        radio.style.borderColor = '#d1d5db'; // Reset all in the group
                                    });
                                    const radioError = form.querySelector(`.error-message[data-field="${field}"]`);
                                    if (radioError) radioError.remove();
                                }
                            }, { once: true });
                        } else {
                            // If field element not found, append error to the general error div
                            errorDiv.textContent += `\n${messages.join(', ')}`;
                        }
                    });
                } else if (data.message) {
                    // Handle other non-validation errors with a 'message' key (e.g., 400 Bad Request, 403 Forbidden, 500 Internal Server Error)
                    errorDiv.textContent = data.message;
                    form.prepend(errorDiv); // Display general error message above the form
                } else {
                    // Fallback for unexpected error structures or network issues (handled by catch block)
                    errorDiv.textContent = `An unexpected error occurred (Status: ${response.status}).`;
                    form.prepend(errorDiv);
                }
            }
        } catch (error) {
            console.error('Fetch Error:', error);
            // This catch block handles network errors, JSON parsing errors, etc.
            const errorDiv = document.createElement('div');
            errorDiv.className = 'form-alert-message'; // Add a class
            errorDiv.style.padding = '1rem';
            errorDiv.style.marginBottom = '1rem';
            errorDiv.style.fontSize = '0.875rem';
            errorDiv.style.color = '#dc2626';
            errorDiv.style.backgroundColor = '#fee2e2';
            errorDiv.style.borderRadius = '0.375rem';
            errorDiv.textContent = 'A network error occurred. Please check your connection and try again.'; // More specific message
            formContainer.innerHTML = ''; // Clear the form container on critical error
            formContainer.appendChild(errorDiv);
        }
    }); --}}
{{-- })(); --}}