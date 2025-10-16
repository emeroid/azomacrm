<x-filament-panels::page>
    {{ $this->form }}

    {{-- This renders the actions defined in getFormActions() --}}
    <x-filament::button wire:click="getFormActions" />
</x-filament-panels::page>
