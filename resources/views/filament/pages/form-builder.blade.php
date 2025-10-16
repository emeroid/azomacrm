<x-filament-panels::page>
    <div class="space-y-6">
        <div class="flex justify-between items-center">
            <h1 class="text-2xl font-bold">Form Builder</h1>
            
            <div class="flex space-x-2">
                <x-filament::button wire:click="togglePreview">
                    {{ $previewMode ? 'Hide Preview' : 'Show Preview' }}
                </x-filament::button>
                
                <x-filament::button type="button" wire:click="saveForm" color="primary">
                    Save Form
                </x-filament::button>
            </div>
        </div>
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="space-y-4 @if($previewMode) lg:col-span-1 @else lg:col-span-2 @endif">
                <x-filament::card>
                    {{ $this->form }}
                </x-filament::card>
            </div>
            
            @if($previewMode)
                <div class="space-y-4">
                    <x-filament::card>
                        <h2 class="text-lg font-medium mb-4">Form Preview</h2>
                        {!! $formPreview !!}
                    </x-filament::card>
                </div>
            @endif
        </div>
        
        @if($showEmbedCode)
            <x-filament::card>
                {!! $embedCode !!}
            </x-filament::card>
        @endif
    </div>
    
    @push('scripts')
        <script>
            document.addEventListener('livewire:load', function () {
                Livewire.on('fieldUpdated', () => {
                    // You can add any JavaScript interactions here
                });
            });
        </script>
    @endpush
</x-filament-panels::page>
