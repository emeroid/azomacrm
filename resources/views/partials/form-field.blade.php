<div class="canvas-field" data-field-id="{{ $field->id }}" 
    style="background-color: white; border: 1px solid #e2e8f0; border-radius: 0.375rem; padding: 1rem; margin-bottom: 0.75rem; position: relative; transition: all 0.2s;"
    onclick="this.style.borderColor='#4f46e5'; this.style.backgroundColor='#f5f3ff'; document.querySelectorAll('.canvas-field').forEach(f => { if(f !== this) { f.style.borderColor='#e2e8f0'; f.style.backgroundColor='white'; } })">
   <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem; padding-bottom: 0.5rem; border-bottom: 1px solid #f3f4f6;">
       <div style="display: flex; align-items: center; gap: 0.5rem;">
           <div style="width: 1.75rem; height: 1.75rem; background-color: #e0e7ff; border-radius: 0.25rem; display: flex; align-items: center; justify-content: center;">
            @php
                switch ($field->type) {
                    case 'text':
                        $iconClass = 'fas fa-font';
                        break;
                    case 'number':
                        $iconClass = 'fas fa-hashtag';
                        break;
                    case 'email':
                        $iconClass = 'fas fa-at';
                        break;
                    case 'tel':
                        $iconClass = 'fas fa-phone';
                        break;
                    case 'textarea':
                        $iconClass = 'fas fa-align-left';
                        break;
                    case 'select':
                        $iconClass = 'fas fa-caret-square-down';
                        break;
                    case 'radio':
                        $iconClass = 'fas fa-dot-circle';
                        break;
                    case 'checkbox':
                        $iconClass = 'fas fa-check-square';
                        break;
                    case 'date':
                        $iconClass = 'fas fa-calendar-alt';
                        break;
                    default:
                        $iconClass = 'fas fa-puzzle-piece';
                }
            @endphp
        
            <i class="{{ $iconClass }}" style="color: #4f46e5; font-size: 0.875rem;"></i>
            </div>
           <span class="field-label" style="font-weight: 500; color: #334155;">{{ $field->label }}</span>
           @if($field->is_required)
               <span style="padding: 0.25rem 0.5rem; background-color: #fee2e2; color: #b91c1c; font-size: 0.75rem; border-radius: 0.25rem; display: inline-flex; align-items: center; gap: 0.25rem;" class="required-badge">
                   Required
               </span>
           @endif
       </div>
       <div style="display: flex; gap: 0.25rem;">
           @if($index > 0)
               <button type="button" style="padding: 0.25rem; color: #6b7280; background: none; border: none; cursor: pointer; border-radius: 0.25rem;"
                       onmouseover="this.style.color='#2563eb'; this.style.backgroundColor='#e0e7ff'" 
                       onmouseout="this.style.color='#6b7280'; this.style.backgroundColor='transparent'"
                       onclick="moveField('{{ $field->id }}', 'up')">
                   <svg style="width: 1rem; height: 1rem;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                       <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                   </svg>
               </button>
           @endif
           @if($index < $total - 1)
               <button type="button" style="padding: 0.25rem; color: #6b7280; background: none; border: none; cursor: pointer; border-radius: 0.25rem;"
                       onmouseover="this.style.color='#2563eb'; this.style.backgroundColor='#e0e7ff'" 
                       onmouseout="this.style.color='#6b7280'; this.style.backgroundColor='transparent'"
                       onclick="moveField('{{ $field->id }}', 'down')">
                   <svg style="width: 1rem; height: 1rem;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                       <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                   </svg>
               </button>
           @endif
           <button type="button" style="padding: 0.25rem; color: #6b7280; background: none; border: none; cursor: pointer; border-radius: 0.25rem;"
                   onmouseover="this.style.color='#dc2626'; this.style.backgroundColor='#fee2e2'" 
                   onmouseout="this.style.color='#6b7280'; this.style.backgroundColor='transparent'"
                   onclick="removeField('{{ $field->id }}')">
               <svg style="width: 1rem; height: 1rem;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                   <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
               </svg>
           </button>
       </div>
   </div>
   <div style="opacity: 0.7; pointer-events: none;">
       {!! $field->renderField() !!}
   </div>
</div>

<script>
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
               'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
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
           'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
           'Accept': 'application/json'
       },
       body: JSON.stringify({
           orders: orderData
       })
   })
   .catch(error => console.error('Error:', error));
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
</script>