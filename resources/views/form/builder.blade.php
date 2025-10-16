<div style="min-height: 100vh; background-color: #f8fafc; font-family: 'Inter', sans-serif;">
    <!-- Header -->
    <div style="background-color: #1e293b; color: white; padding: 1rem 2rem;">
        <div style="max-width: 1800px; margin: 0 auto; display: flex; justify-content: space-between; align-items: center;">
            <h1 style="font-size: 1.5rem; font-weight: 600; display: flex; align-items: center; gap: 0.5rem;">
                <svg style="width: 1.5rem; height: 1.5rem;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                Form Builder - {{ $template->name }}
            </h1>
            <div style="display: flex; gap: 1rem;">
                <button id="saveForm" style="padding: 0.5rem 1.25rem; background-color: #047857; color: white; border-radius: 0.375rem; font-weight: 500; display: flex; align-items: center; gap: 0.5rem; transition: all 0.2s;"
                        onmouseover="this.style.backgroundColor='#065f46'; this.style.transform='translateY(-1px)'" 
                        onmouseout="this.style.backgroundColor='#047857'; this.style.transform='translateY(0)'">
                    <svg style="width: 1rem; height: 1rem;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Save Form
                </button>
                <button id="generateEmbed" style="padding: 0.5rem 1.25rem; background-color: #1d4ed8; color: white; border-radius: 0.375rem; font-weight: 500; display: flex; align-items: center; gap: 0.5rem; transition: all 0.2s;"
                        onmouseover="this.style.backgroundColor='#1e40af'; this.style.transform='translateY(-1px)'" 
                        onmouseout="this.style.backgroundColor='#1d4ed8'; this.style.transform='translateY(0)'">
                    <svg style="width: 1rem; height: 1rem;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                    </svg>
                    Embed Code
                </button>
                <button class="preview-btn" style="padding: 0.5rem 1.25rem; background-color: #7c3aed; color: white; border-radius: 0.375rem; font-weight: 500; display: flex; align-items: center; gap: 0.5rem; transition: all 0.2s;"
                        onmouseover="this.style.backgroundColor='#6d28d9'; this.style.transform='translateY(-1px)'" 
                        onmouseout="this.style.backgroundColor='#7c3aed'; this.style.transform='translateY(0)'">
                    <svg style="width: 1rem; height: 1rem;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    Preview
                </button>
            </div>
        </div>
    </div>

    <!-- Main Content - Three Panels -->
    <div style="max-width: 1800px; margin: 0 auto; padding: 1.5rem; display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1.5rem; min-height: calc(100vh - 74px);">
        <!-- Left Panel - Available Fields -->
        <div style="background-color: white; border-radius: 0.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); display: flex; flex-direction: column;">
            <div style="padding: 0.75rem 1rem; border-bottom: 1px solid #e2e8f0; font-weight: 500; color: #334155; display: flex; align-items: center; gap: 0.5rem;">
                <svg style="width: 1.25rem; height: 1.25rem; color: #4f46e5;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                Available Fields
            </div>
            <div style="padding: 1rem; flex-grow: 1; overflow-y: auto; display: grid; grid-template-columns: 1fr; gap: 0.75rem;">
                @foreach($fieldTypes as $type => $config)
                    <div class="available-field" data-type="{{ $type }}" 
                         style="padding: 0.75rem; background-color: white; border: 1px solid #e2e8f0; border-radius: 0.375rem; cursor: pointer; display: flex; align-items: center; gap: 0.75rem; transition: all 0.2s;"
                         onmouseover="this.style.borderColor='#93c5fd'; this.style.backgroundColor='#eff6ff'; this.style.transform='translateX(2px)'" 
                         onmouseout="this.style.borderColor='#e2e8f0'; this.style.backgroundColor='white'; this.style.transform='translateX(0)'"
                         onclick="addField('{{ $type }}')"
                    >
                        <div style="width: 2rem; height: 2rem; background-color: #e0e7ff; border-radius: 0.25rem; display: flex; align-items: center; justify-content: center;">
                            <i class="{{ $config['icon'] }}" style="color: #4f46e5; font-size: 1rem;"></i>
                        </div>
                        <div>
                            <div style="font-weight: 500; color: #334155;">{{ $config['label'] }}</div>
                            <div style="font-size: 0.75rem; color: #64748b;">{{ $config['description'] ?? 'Form field' }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Middle Panel - Form Canvas -->
        <div style="background-color: white; border-radius: 0.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); display: flex; flex-direction: column;">
            <div style="padding: 0.75rem 1rem; border-bottom: 1px solid #e2e8f0; font-weight: 500; color: #334155; display: flex; align-items: center; gap: 0.5rem;">
                <svg style="width: 1.25rem; height: 1.25rem; color: #4f46e5;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Form Canvas
            </div>
            <div style="padding: 1rem; flex-grow: 1; overflow-y: auto;" id="formCanvas">
                @foreach($template->fields as $index => $field)
                    @include('partials.form-field', ['field' => $field, 'index' => $index, 'total' => count($template->fields)])
                @endforeach
            </div>
        </div>

        <!-- Right Panel - Field Properties -->
        <div style="background-color: white; border-radius: 0.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); display: flex; flex-direction: column;">
            <div style="padding: 0.75rem 1rem; border-bottom: 1px solid #e2e8f0; font-weight: 500; color: #334155; display: flex; align-items: center; gap: 0.5rem;">
                <svg style="width: 1.25rem; height: 1.25rem; color: #4f46e5;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                Field Properties
            </div>
            <div style="padding: 1.5rem; flex-grow: 1; overflow-y: auto;" id="fieldProperties">
                <div class="empty-state" style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%; color: #64748b; text-align: center;">
                    <svg style="width: 3rem; height: 3rem; color: #cbd5e1; margin-bottom: 1rem;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <h3 style="font-size: 1.125rem; font-weight: 500; margin-bottom: 0.5rem;">No Field Selected</h3>
                    <p style="max-width: 300px;">Click on a field in the canvas to edit its properties</p>
                </div>
                <form id="propertiesForm" style="display: none;"></form>
            </div>
        </div>
    </div>
</div>

<!-- Preview Modal -->
<div id="previewModal" style="position: fixed; inset: 0; background-color: rgba(0,0,0,0.5); display: none; align-items: center; justify-content: center; z-index: 50;">
    <div style="background-color: white; border-radius: 0.5rem; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1); width: 100%; max-width: 56rem; max-height: 90vh; overflow: auto;">
        <div style="padding: 1rem 1.5rem; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center;">
            <h3 style="font-size: 1.25rem; font-weight: 600;">Form Preview</h3>
            <button type="button" style="color: #9ca3af; background: none; border: none; cursor: pointer;" onclick="document.getElementById('previewModal').style.display='none'">
                <svg style="width: 1.5rem; height: 1.5rem;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <div style="padding: 1.5rem;">
            <form id="previewForm">
                <!-- Preview will be loaded here -->
            </form>
        </div>
        <div style="padding: 1rem 1.5rem; border-top: 1px solid #e5e7eb; display: flex; justify-content: flex-end;">
            <button type="button" 
                    style="padding: 0.5rem 1rem; background-color: #4b5563; color: white; border-radius: 0.375rem; font-weight: 500; border: none; cursor: pointer;"
                    onclick="document.getElementById('previewModal').style.display='none'">
                Close
            </button>
        </div>
    </div>
</div>

<!-- Embed Code Modal -->
<div id="embedModal" style="position: fixed; inset: 0; background-color: rgba(0,0,0,0.5); display: none; align-items: center; justify-content: center; z-index: 50;">
    <div style="background-color: white; border-radius: 0.5rem; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1); width: 100%; max-width: 42rem;">
        <div style="padding: 1rem 1.5rem; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center;">
            <h3 style="font-size: 1.25rem; font-weight: 600;">Embed Code</h3>
            <button type="button" style="color: #9ca3af; background: none; border: none; cursor: pointer;" onclick="document.getElementById('embedModal').style.display='none'">
                <svg style="width: 1.5rem; height: 1.5rem;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <div style="padding: 1.5rem;">
            <p style="color: #6b7280; margin-bottom: 0.5rem;">Copy this code and paste it into your website:</p>
            <textarea id="embedCode" style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 0.375rem; font-family: 'Courier New', monospace; font-size: 0.875rem; min-height: 120px; resize: none;" readonly></textarea>
            <div style="margin-top: 1rem; padding: 0.75rem; background-color: #f3f4f6; border-radius: 0.375rem;">
                <h4 style="font-size: 0.875rem; font-weight: 600; margin-bottom: 0.5rem; color: #374151;">Implementation Notes:</h4>
                <ul style="font-size: 0.875rem; color: #4b5563; list-style-type: disc; padding-left: 1.25rem;">
                    <li>Paste this code where you want the form to appear</li>
                    <li>Make sure the target page has enough width for the form</li>
                    <li>Form submissions will be saved to your dashboard</li>
                </ul>
            </div>
        </div>
        <div style="padding: 1rem 1.5rem; border-top: 1px solid #e5e7eb; display: flex; justify-content: flex-end; gap: 0.5rem;">
            <button type="button" 
                    style="padding: 0.5rem 1rem; background-color: #4b5563; color: white; border-radius: 0.375rem; font-weight: 500; border: none; cursor: pointer;"
                    onclick="document.getElementById('embedModal').style.display='none'">
                Close
            </button>
            <button type="button" 
                    style="padding: 0.5rem 1rem; background-color: #2563eb; color: white; border-radius: 0.375rem; font-weight: 500; border: none; cursor: pointer;"
                    id="copyEmbedCode">
                Copy Code
            </button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // CSRF Token for AJAX requests
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    let currentFieldId = null;

    // Initialize the form builder
    initFormBuilder();

    function initFormBuilder() {
        setupModals();
        setupFieldInteractions();
        setupFormActions();
    }

    function setupModals() {
        // Preview modal
        document.querySelector('.preview-btn').addEventListener('click', function() {
            generatePreview();
            document.getElementById('previewModal').style.display = 'flex';
        });

        // Embed modal
        document.getElementById('generateEmbed').addEventListener('click', function() {
            generateEmbedCode();
        });

        // Copy embed code
        document.getElementById('copyEmbedCode').addEventListener('click', function() {
            const embedCode = document.getElementById('embedCode');
            embedCode.select();
            document.execCommand('copy');
            
            const btn = this;
            btn.textContent = 'Copied!';
            btn.style.backgroundColor = '#047857';
            setTimeout(() => {
                btn.textContent = 'Copy Code';
                btn.style.backgroundColor = '#2563eb';
            }, 2000);
        });
    }

    function setupFieldInteractions() {
        // Field selection in canvas
        document.getElementById('formCanvas').addEventListener('click', function(e) {
            const fieldElement = e.target.closest('.canvas-field');
            if (fieldElement) {
                currentFieldId = fieldElement.dataset.fieldId;
                loadFieldProperties(currentFieldId);
                
                // Highlight selected field
                document.querySelectorAll('.canvas-field').forEach(field => {
                    field.style.borderColor = '#e2e8f0';
                    field.style.backgroundColor = 'white';
                });
                fieldElement.style.borderColor = '#4f46e5';
                fieldElement.style.backgroundColor = '#f5f3ff';
            }
        });
    }

    function setupFormActions() {
        // Save form
        document.getElementById('saveForm').addEventListener('click', saveForm);
    }

    // Add new field to canvas
    window.addField = function(fieldType) {
        fetch("{{ route('form.builder.field') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                template_id: "{{ $template->id }}",
                type: fieldType
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const formCanvas = document.getElementById('formCanvas');
                const fieldElement = createFieldElement(data.field);
                formCanvas.appendChild(fieldElement);
                updateFieldOrders();
                
                // Select the new field
                currentFieldId = data.field.id;
                loadFieldProperties(currentFieldId);
                fieldElement.style.borderColor = '#4f46e5';
                fieldElement.style.backgroundColor = '#f5f3ff';
            }
        })
        .catch(error => console.error('Error:', error));
    }

    function loadFieldProperties(fieldId) {
        fetch(`{{ route('form.builder.properties') }}?field_id=${fieldId}`, {
            headers: {
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.html) {
                const propertiesContainer = document.getElementById('fieldProperties');
                propertiesContainer.querySelector('.empty-state').style.display = 'none';
                
                const propertiesForm = document.getElementById('propertiesForm');
                propertiesForm.innerHTML = data.html;
                propertiesForm.style.display = 'block';
                
                // Setup form submission
                propertiesForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    updateFieldProperties(fieldId);
                });
            }
        })
        .catch(error => console.error('Error:', error));
    }

    function updateFieldProperties(fieldId) {
        const formData = new FormData(document.getElementById('propertiesForm'));
        
        fetch("{{ route('form.builder.properties.update') }}", {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update the field in the canvas
                const fieldElement = document.querySelector(`.canvas-field[data-field-id="${fieldId}"]`);
                if (fieldElement) {
                    fieldElement.querySelector('.field-label').textContent = data.field.label;
                    if (data.field.is_required) {
                        fieldElement.querySelector('.required-badge').style.display = 'inline-flex';
                    } else {
                        fieldElement.querySelector('.required-badge').style.display = 'none';
                    }
                }
                showToast('Field properties updated successfully!', 'success');
            }
        })
        .catch(error => console.error('Error:', error));
    }

    function moveField(fieldId, direction) {
        const fieldElement = document.querySelector(`.canvas-field[data-field-id="${fieldId}"]`);
        if (!fieldElement) return;

        if (direction === 'up' && fieldElement.previousElementSibling) {
            fieldElement.parentNode.insertBefore(fieldElement, fieldElement.previousElementSibling);
        } else if (direction === 'down' && fieldElement.nextElementSibling) {
            fieldElement.parentNode.insertBefore(fieldElement.nextElementSibling, fieldElement);
        }

        updateFieldOrders();
    }

    function removeField(fieldId) {
        if (confirm('Are you sure you want to remove this field?')) {
            fetch("{{ route('form.builder.field') }}", {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    field_id: fieldId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.querySelector(`.canvas-field[data-field-id="${fieldId}"]`).remove();
                    updateFieldOrders();
                    document.getElementById('fieldProperties').querySelector('.empty-state').style.display = 'flex';
                    document.getElementById('propertiesForm').style.display = 'none';
                    showToast('Field removed successfully!', 'success');
                }
            })
            .catch(error => console.error('Error:', error));
        }
    }

    function updateFieldOrders() {
        const orderData = [];
        const formCanvas = document.getElementById('formCanvas');
        
        formCanvas.querySelectorAll('.canvas-field').forEach((field, index) => {
            orderData.push({
                field_id: field.dataset.fieldId,
                order: index + 1
            });
        });

        fetch("{{ route('form.builder.reorder') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                orders: orderData
            })
        })
        .catch(error => console.error('Error:', error));
    }

    function generatePreview() {
        fetch("{{ route('form.builder.preview') }}?template_id={{ $template->id }}", {
            headers: {
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.html) {
                document.getElementById('previewForm').innerHTML = data.html;
            }
        })
        .catch(error => console.error('Error:', error));
    }

    function generateEmbedCode() {
        fetch("{{ route('form.builder.embed') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                template_id: "{{ $template->id }}"
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.embed_code) {
                document.getElementById('embedCode').value = data.embed_code;
                document.getElementById('embedModal').style.display = 'flex';
            }
        })
        .catch(error => console.error('Error:', error));
    }

    function saveForm() {
        fetch("{{ route('form.builder.save') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                template_id: "{{ $template->id }}"
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Form saved successfully!', 'success');
            }
        })
        .catch(error => console.error('Error:', error));
    }

    // Helper Functions
    function createFieldElement(fieldData) {
        const div = document.createElement('div');
        div.className = 'canvas-field';
        div.dataset.fieldId = fieldData.id;
        div.style.backgroundColor = 'white';
        div.style.border = '1px solid #e2e8f0';
        div.style.borderRadius = '0.375rem';
        div.style.padding = '1rem';
        div.style.marginBottom = '0.75rem';
        div.style.position = 'relative';
        div.style.transition = 'all 0.2s';
        div.innerHTML = `
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem; padding-bottom: 0.5rem; border-bottom: 1px solid #f3f4f6;">
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <div style="width: 1.75rem; height: 1.75rem; background-color: #e0e7ff; border-radius: 0.25rem; display: flex; align-items: center; justify-content: center;">
                        <i class="${getFieldIcon(fieldData.type)}" style="color: #4f46e5; font-size: 0.875rem;"></i>
                    </div>
                    <span class="field-label" style="font-weight: 500; color: #334155;">${fieldData.label}</span>
                    ${fieldData.is_required ? '<span style="padding: 0.25rem 0.5rem; background-color: #fee2e2; color: #b91c1c; font-size: 0.75rem; border-radius: 0.25rem; display: inline-flex; align-items: center; gap: 0.25rem;" class="required-badge">Required</span>' : ''}
                </div>
                <div style="display: flex; gap: 0.25rem;">
                    <button type="button" style="padding: 0.25rem; color: #6b7280; background: none; border: none; cursor: pointer; border-radius: 0.25rem;"
                            onmouseover="this.style.color='#2563eb'; this.style.backgroundColor='#e0e7ff'" 
                            onmouseout="this.style.color='#6b7280'; this.style.backgroundColor='transparent'"
                            onclick="moveField('${fieldData.id}', 'up')">
                        <svg style="width: 1rem; height: 1rem;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                        </svg>
                    </button>
                    <button type="button" style="padding: 0.25rem; color: #6b7280; background: none; border: none; cursor: pointer; border-radius: 0.25rem;"
                            onmouseover="this.style.color='#2563eb'; this.style.backgroundColor='#e0e7ff'" 
                            onmouseout="this.style.color='#6b7280'; this.style.backgroundColor='transparent'"
                            onclick="moveField('${fieldData.id}', 'down')">
                        <svg style="width: 1rem; height: 1rem;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <button type="button" style="padding: 0.25rem; color: #6b7280; background: none; border: none; cursor: pointer; border-radius: 0.25rem;"
                            onmouseover="this.style.color='#dc2626'; this.style.backgroundColor='#fee2e2'" 
                            onmouseout="this.style.color='#6b7280'; this.style.backgroundColor='transparent'"
                            onclick="removeField('${fieldData.id}')">
                        <svg style="width: 1rem; height: 1rem;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </button>
                </div>
            </div>
            <div style="opacity: 0.7; pointer-events: none;">
                ${fieldData.rendered_field}
            </div>
        `;
        
        return div;
    }

    function getFieldIcon(fieldType) {
        const icons = {
            'text': 'fas fa-font',
            'number': 'fas fa-hashtag',
            'email': 'fas fa-at',
            'tel': 'fas fa-phone',
            'textarea': 'fas fa-align-left',
            'select': 'fas fa-caret-square-down',
            'radio': 'fas fa-dot-circle',
            'checkbox': 'fas fa-check-square',
            'date': 'fas fa-calendar-alt',
            'product_selector': 'fas fa-boxes'
        };
        return icons[fieldType] || 'fas fa-puzzle-piece';
    }

    function showToast(message, type) {
        const toast = document.createElement('div');
        toast.style.position = 'fixed';
        toast.style.bottom = '1.5rem';
        toast.style.right = '1.5rem';
        toast.style.padding = '0.75rem 1.25rem';
        toast.style.borderRadius = '0.375rem';
        toast.style.color = 'white';
        toast.style.fontWeight = '500';
        toast.style.boxShadow = '0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06)';
        toast.style.zIndex = '100';
        toast.style.transition = 'all 0.3s ease';
        toast.style.transform = 'translateY(100px)';
        toast.style.opacity = '0';
        
        if (type === 'success') {
            toast.style.backgroundColor = '#047857';
        } else {
            toast.style.backgroundColor = '#b91c1c';
        }
        
        toast.textContent = message;
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.style.transform = 'translateY(0)';
            toast.style.opacity = '1';
        }, 10);
        
        setTimeout(() => {
            toast.style.transform = 'translateY(100px)';
            toast.style.opacity = '0';
            setTimeout(() => {
                document.body.removeChild(toast);
            }, 300);
        }, 3000);
    }
});
</script>