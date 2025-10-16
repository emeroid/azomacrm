<div class="space-y-4">
    <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
        <pre class="whitespace-pre-wrap font-mono text-sm">{{ $orderInfo }}</pre>
    </div>
    
    <div class="flex justify-end">
        <div x-data="{
                async copyToClipboard() {
                    try {
                        await navigator.clipboard.writeText(@js($orderInfo));
                        
                        // Correct notification in Filament v3
                        $wire.dispatch('notify', {
                            title: 'Copied!',
                            body: 'Order information copied to clipboard',
                            status: 'success'
                        });
                        
                        // Close modal
                        $dispatch('close-modal', { id: 'x-filament-modal' });
                    } catch (err) {
                        $wire.dispatch('notify', {
                            title: 'Error',
                            body: 'Failed to copy to clipboard',
                            status: 'danger'
                        });
                    }
                }
        }"
        >
        <x-filament::button
            icon="heroicon-o-clipboard"
            x-on:click="copyToClipboard()"
        >
            Copy to Clipboard
        </x-filament::button>
        </div>
    </div>
</div>