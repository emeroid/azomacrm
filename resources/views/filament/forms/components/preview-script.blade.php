<!-- preview-script.blade.php -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        function setupPreviewObserver() {
            try {
                const observer = new MutationObserver(function(mutations) {
                    mutations.forEach(function(mutation) {
                        if (mutation.type === 'attributes' && mutation.attributeName === 'wire:key') {
                            if (window.Livewire) {
                                Livewire.emit('refreshPreview');
                            }
                        }
                    });
                });
                
                const formContainer = document.querySelector('[wire\\:key="form-content"]');
                if (formContainer) {
                    observer.observe(formContainer, { 
                        attributes: true,
                        attributeFilter: ['wire:key']
                    });
                }
                
                // Fallback for immediate updates
                Livewire.hook('message.processed', () => {
                    setTimeout(() => {
                        if (window.Livewire) {
                            Livewire.emit('refreshPreview');
                        }
                    }, 100);
                });
                
            } catch (error) {
                console.error('Preview observer error:', error);
            }
        }
        
        // Wait for Livewire to be available
        if (window.Livewire) {
            setupPreviewObserver();
        } else {
            document.addEventListener('livewire:load', setupPreviewObserver);
        }
    });
</script>