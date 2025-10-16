
<x-filament-panels::page>
        @push('scripts')
            
                <script src="{{ asset('build/assets/embed/order-form.js') }}" defer></script>
                <link href="{{ asset('build/assets/embed/order-form.css') }}" rel="stylesheet" />
        
        @endpush
    
        <div class="space-y-6">
            <x-filament::section>
                <div class="space-y-4">
                    <h2 class="text-xl font-bold">Embed Code for: {{ $record->title }}</h2>
                    
                    <div class="relative">
                        {{-- Show encoded HTML in pre block --}}
                        <pre class="p-4 bg-gray-100 dark:bg-gray-800 rounded-lg overflow-x-auto text-sm">
                    <code class="language-html">
                    &lt;div id="order-form-container" 
                        data-form-id="{{ $record->id }}"
                        data-redirect-url="{{ $record->redirect_url ?? ''  }}"
                        data-api-base="{{ config('app.url') }}"&gt;&lt;/div&gt;
                    &lt;link rel="stylesheet" href="{{ url('/build/assets/embed/order-form.css') }}"&gt;
                    &lt;script src="{{ url('/build/assets/embed/order-form.js') }}"&gt;&lt;/script&gt;
                    </code>
                        </pre>
                    
                        {{-- Copy raw HTML using Alpine.js --}}
                        <div x-data="{
                            copyEmbedCode() {
                                const code = `<div id='order-form-container'
                        data-form-id='{{ $record->id }}'
                        data-redirect-url='{{ $record->redirect_url }}'
                        data-api-base='{{ config('app.url') }}'></div>
                    <link rel='stylesheet' href='{{ url('/build/assets/embed/order-form.css') }}'>
                    <script src='{{ url('/build/assets/embed/order-form.js') }}'></script>`;
                                navigator.clipboard.writeText(code).then(() => {
                                    $tooltip('Copied!');
                                });
                            }
                        }">
                    
                            <x-filament::button
                                icon="heroicon-o-clipboard"
                                color="gray"
                                size="sm"
                                class="absolute top-2 right-2"
                                x-on:click="copyEmbedCode()"
                                x-tooltip="'Copy to clipboard'">
                                Copy
                            </x-filament::button>
                        </div>
                    </div>
                </div>
            </x-filament::section>
            
            <x-filament::section>
                <div class="space-y-4">
                    <h2 class="text-xl font-bold">Live Preview</h2>
                    <div class="border rounded-lg p-4 bg-gray-50 dark:bg-gray-900">
                        <div 
                            id="order-form-container" 
                            data-form-id="{{ $record->id }}"
                            data-redirect-url="{{ $record->redirect_url }}">
                        </div>
                    </div>
                </div>
            </x-filament::section>
        </div>
</x-filament-panels::page>
