document.addEventListener('DOMContentLoaded', function() {
    // Modal elements
    const previewModal = document.getElementById('previewModal');
    const embedModal = document.getElementById('embedModal');
    
    // Close modal functions
    function closePreviewModal() {
        previewModal.classList.add('hidden');
    }
    
    function closeEmbedModal() {
        embedModal.classList.add('hidden');
    }
    
    // Event listeners for modals
    document.getElementById('closePreviewModal').addEventListener('click', closePreviewModal);
    document.getElementById('closePreviewModalBtn').addEventListener('click', closePreviewModal);
    document.getElementById('closeEmbedModal').addEventListener('click', closeEmbedModal);
    document.getElementById('closeEmbedModalBtn').addEventListener('click', closeEmbedModal);
    
    // Preview button
    document.querySelector('.preview-btn').addEventListener('click', function() {
        generatePreview();
        previewModal.classList.remove('hidden');
    });
    
    // Generate embed code
    document.getElementById('generateEmbed').addEventListener('click', function() {
        generateEmbedCode();
        embedModal.classList.remove('hidden');
    });
    
    // Copy embed code
    document.getElementById('copyEmbedCode').addEventListener('click', function() {
        const embedCode = document.getElementById('embedCode');
        embedCode.select();
        document.execCommand('copy');
        
        const btn = document.getElementById('copyEmbedCode');
        btn.textContent = 'Copied!';
        btn.classList.add('bg-green-600');
        setTimeout(() => {
            btn.textContent = 'Copy Code';
            btn.classList.remove('bg-green-600');
        }, 2000);
    });
    
    // Drag and drop functionality
    const fieldItems = document.querySelectorAll('.field-item');
    const formCanvas = document.getElementById('formCanvas');
    
    fieldItems.forEach(item => {
        item.addEventListener('dragstart', function(e) {
            e.dataTransfer.setData('text/plain', this.dataset.type);
            this.classList.add('opacity-50');
        });
        
        item.addEventListener('dragend', function() {
            this.classList.remove('opacity-50');
        });
    });
    
    formCanvas.addEventListener('dragover', function(e) {
        e.preventDefault();
        const afterElement = getDragAfterElement(formCanvas, e.clientY);
        const placeholder = document.querySelector('.form-field-placeholder');
        
        if (!placeholder) {
            const placeholder = document.createElement('div');
            placeholder.classList.add('form-field-placeholder', 'h-16', 'my-2', 'rounded-md');
            formCanvas.insertBefore(placeholder, afterElement);
        }
    });
    
    formCanvas.addEventListener('dragleave', function() {
        const placeholder = document.querySelector('.form-field-placeholder');
        if (placeholder) {
            placeholder.remove();
        }
    });
    
    formCanvas.addEventListener('drop', function(e) {
        e.preventDefault();
        const placeholder = document.querySelector('.form-field-placeholder');
        if (placeholder) {
            placeholder.remove();
        }
        
        const fieldType = e.dataTransfer.getData('text/plain');
        addNewField(fieldType);
    });
    
    function getDragAfterElement(container, y) {
        const draggableElements = [...container.querySelectorAll('.form-field:not(.form-field-placeholder)')];
        
        return draggableElements.reduce((closest, child) => {
            const box = child.getBoundingClientRect();
            const offset = y - box.top - box.height / 2;
            
            if (offset < 0 && offset > closest.offset) {
                return { offset: offset, element: child };
            } else {
                return closest;
            }
        }, { offset: Number.NEGATIVE_INFINITY }).element;
    }
    
    // Field editing
    formCanvas.addEventListener('click', function(e) {
        if (e.target.classList.contains('btn-edit') || e.target.closest('.btn-edit')) {
            const fieldElement = e.target.closest('.form-field');
            loadFieldProperties(fieldElement);
        }
        
        if (e.target.classList.contains('btn-remove') || e.target.closest('.btn-remove')) {
            if (confirm('Are you sure you want to remove this field?')) {
                const fieldElement = e.target.closest('.form-field');
                fieldElement.remove();
                updateFieldOrders();
            }
        }
    });
    
    // Save form
    document.getElementById('saveForm').addEventListener('click', saveForm);
    
    // Helper functions
    function addNewField(fieldType) {
        // AJAX call to add new field
        console.log('Adding new field of type:', fieldType);
    }
    
    function loadFieldProperties(fieldElement) {
        // AJAX call to load field properties
        console.log('Loading properties for field:', fieldElement.dataset.fieldId);
    }
    
    function updateFieldOrders() {
        // AJAX call to update field orders
        console.log('Updating field orders');
    }
    
    function generatePreview() {
        // AJAX call to generate preview
        console.log('Generating form preview');
    }
    
    function generateEmbedCode() {
        // AJAX call to generate embed code
        console.log('Generating embed code');
        document.getElementById('embedCode').value = '<div id="embedded-form-container"></div>\n<script src="' + window.location.origin + '/embed/form-slug"></script>';
    }
    
    function saveForm() {
        // AJAX call to save form
        console.log('Saving form');
    }
});