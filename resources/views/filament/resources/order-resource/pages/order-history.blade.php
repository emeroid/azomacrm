<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Communication History -->
        <div class="bg-white rounded-lg shadow p-4 space-y-4 max-h-96 overflow-y-auto">
            @foreach ($this->communications as $communication)
                <div class="@if($communication->sender_id === auth()->id()) bg-primary-50 @else bg-gray-50 @endif rounded-lg p-4">
                    <div class="flex justify-between items-start">
                        <div class="font-medium">
                            {{ $communication->sender?->name }}
                            @if($communication->agent_id === $communication->sender_id)
                                <span class="text-xs text-primary-600">(Agent)</span>
                            @endif
                        </div>
                        <div class="text-xs text-gray-500">
                            {{ $communication->created_at->format('M j, Y g:i A') }}
                            @if($communication->type)
                                • <span class="capitalize">{{ $communication->type }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="mt-2 text-sm">
                        {{ $communication->content }}
                    </div>
                    @if($communication->outcome || !empty($communication->labels))
                        <div class="mt-2 flex flex-wrap gap-2">
                            @if($communication->outcome)
                                <span class="px-2 py-1 text-xs rounded-full bg-primary-100 text-primary-800">
                                    {{ str_replace('_', ' ', $communication->outcome) }}
                                </span>
                            @endif
                            
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        {{ $this->communications->links() }}

        <!-- Message Form -->
        <form wire:submit.prevent="sendMessage" class="bg-white rounded-lg shadow p-4">
            {{ $this->form }}

            <div class="flex justify-end mt-4">
                <x-filament::button type="submit">
                    Send Message
                </x-filament::button>
            </div>
        </form>
    </div>
</x-filament-panels::page>
