<x-filament-widgets::widget>
    <x-filament::card>
        <div class="flex items-center justify-between gap-4">
            <h2 class="text-lg font-semibold">Filters</h2>
        </div>

        <form wire:submit.prevent class="mt-4 space-y-4">
            {{ $this->form }}
        </form>
    </x-filament::card>
</x-filament-widgets::widget>
