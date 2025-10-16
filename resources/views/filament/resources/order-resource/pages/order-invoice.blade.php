<x-filament-panels::page>
    <div class="fi-page-invoice bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <!-- Header with print button -->
        <div class="fi-page-header p-6 bg-primary-600 dark:bg-primary-700 text-white flex justify-between items-center print:hidden">
            <div>
                <h1 class="text-2xl font-bold">{{ __('Order Invoice') }}</h1>
                <p class="text-primary-100 dark:text-primary-200 opacity-90 mt-1">{{ __('Invoice #:number', ['number' => $record->order_number]) }}</p>
            </div>
            <x-filament::button
                icon="heroicon-o-printer"
                x-on:click="window.print()" 
                class="bg-gray-400 dark:bg-gray-800 text-primary-600 dark:text-primary-200 hover:bg-gray-200 dark:hover:bg-gray-700"
            >
                {{ __('Print Invoice') }}
            </x-filament::button>
        </div>

        <div class="print:block p-6 sm:p-8">
            <!-- Company Header -->
            <div class="text-center mb-8 border-b border-gray-200 dark:border-gray-700 pb-6">
                <div class="inline-flex items-center justify-center bg-primary-50 dark:bg-primary-900/30 rounded-full p-4 mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-primary-600 dark:text-primary-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <h2 class="text-2xl sm:text-3xl font-bold text-gray-800 dark:text-white">Azoma CRM</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">Wurno Plaza, By Ibrahim Waziri Crescent</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">Near Federal Government Boys College, Gudu - Abuja. </p>
            </div>

            <!-- Invoice Details -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <div class="bg-gray-50 dark:bg-gray-800 p-5 rounded-lg">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-3">{{ __('INVOICE TO:') }}</h3>
                    <p class="text-base font-medium text-gray-900 dark:text-white mb-1">{{ $record->full_name }}</p>
                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ $record->address }}, {{ $record->state }}</p>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">{{ $record->mobile }}</p>
                    @if ($record->email)
                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ $record->email }}</p>
                    @endif
                </div>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="bg-primary-50 dark:bg-primary-900/30 p-4 rounded-lg">
                        <h3 class="text-xs font-semibold text-primary-700 dark:text-primary-300 uppercase tracking-wider mb-2">{{ __('INVOICE #') }}</h3>
                        <p class="text-base font-bold text-primary-900 dark:text-primary-100">{{ $record->order_number }}</p>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-800 p-4 rounded-lg">
                        <h3 class="text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-2">{{ __('DATE ISSUED') }}</h3>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $record->created_at->format('M d, Y') }}</p>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-800 p-4 rounded-lg sm:col-span-2">
                        <h3 class="text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-2">{{ __('STATUS') }}</h3>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900/40 text-green-800 dark:text-green-300">
                            
                            {{ strtoupper($record->status) }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Items Table -->
            <div class="mb-8">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ __('Invoice Details') }}</h3>
                <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700 fi-ta-table-container">
                    <table class="fi-table fi-ta-table w-full divide-y divide-gray-200 dark:divide-gray-700 text-start">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr class="divide-x divide-gray-200 dark:divide-gray-700">
                                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Product') }}</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Quantity') }}</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Unit Price') }}</th>
                                <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Total') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-900">
                            @php
                                $grandTotal = 0;
                            @endphp
                            @foreach ($record->items as $item)
                                <tr class="divide-x divide-gray-200 dark:divide-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $item->product->name }}</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ __('SKU: :sku', ['sku' => $item->product->sku ?? 'N/A']) }}</div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ $record->isOrderByCustomer() ? $record->getSourceFormByNote() : $item->quantity }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        ₦{{ number_format($item->unit_price, 2) }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm font-semibold text-right text-gray-900 dark:text-white">
                                        ₦{{ number_format($item->total_price, 2) }}
                                    </td>
                                </tr>
                                @php
                                    $grandTotal += $item->total_price;
                                @endphp
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Totals -->
            <div class="flex justify-end">
                <div class="w-full md:w-1/2">
                    <div class="space-y-2">
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-500 dark:text-gray-400">{{ __('Subtotal') }}</span>
                            <span class="text-sm text-gray-700 dark:text-gray-300">₦{{ number_format($grandTotal, 2) }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-500 dark:text-gray-400">{{ __('Tax (0%)') }}</span>
                            <span class="text-sm text-gray-700 dark:text-gray-300">₦0.00</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-500 dark:text-gray-400">{{ __('Discount') }}</span>
                            <span class="text-sm text-gray-700 dark:text-gray-300">₦0.00</span>
                        </div>
                        <div class="border-t border-gray-200 dark:border-gray-700 pt-2 mt-2">
                            <div class="flex justify-between items-center">
                                <span class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('GRAND TOTAL') }}</span>
                                <span class="text-xl font-bold text-primary-600 dark:text-primary-400">₦{{ number_format($grandTotal, 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="mt-12 pt-8 border-t border-gray-200 dark:border-gray-700">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">{{ __('Payment Method') }}</h4>
                        <div class="flex items-center">
                            <div class="bg-gray-100 dark:bg-gray-800 p-2 rounded-md mr-3">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                </svg>
                            </div>
                            <span class="text-sm text-gray-600 dark:text-gray-400">{{ __('Pay on Delivery') }}</span>
                        </div>
                    </div>
                    <div>
                        <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">{{ __('Terms & Notes') }}</h4>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Payment is due upon delivery of this product. Thank you for your business!') }}</p>
                    </div>
                </div>
                
                <div class="mt-6 text-center">
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('If you have any questions about this invoice, please contact support@azomacrm.com') }}</p>
                </div>
            </div>
        </div>
    </div>

    <style>
        @media print {
            body, html {
                background: white !important;
                color: black !important;
            }
            .fi-page-invoice {
                border: none !important;
                box-shadow: none !important;
            }
            .bg-primary-600, .dark\:bg-primary-700 {
                background-color: #4f46e5 !important;
                -webkit-print-color-adjust: exact;
                color: white !important;
            }
            .text-primary-100, .dark\:text-primary-200 {
                color: #e0e7ff !important;
                -webkit-print-color-adjust: exact;
            }
            .bg-primary-50, .dark\:bg-primary-900\/30 {
                background-color: #eef2ff !important;
                -webkit-print-color-adjust: exact;
            }
            .text-primary-600, .dark\:text-primary-400,
            .text-primary-700, .dark\:text-primary-300,
            .text-primary-900, .dark\:text-primary-100 {
                color: #3730a3 !important;
                -webkit-print-color-adjust: exact;
            }
            .bg-gray-50, .dark\:bg-gray-800 {
                background-color: #f9fafb !important;
                -webkit-print-color-adjust: exact;
            }
            .bg-green-100, .dark\:bg-green-900\/40 {
                background-color: #d1fae5 !important;
                -webkit-print-color-adjust: exact;
            }
            .text-green-800, .dark\:text-green-300 {
                color: #065f46 !important;
                -webkit-print-color-adjust: exact;
            }
            .border-gray-200, .dark\:border-gray-700 {
                border-color: #e5e7eb !important;
            }
            .text-gray-900, .dark\:text-white {
                color: #111827 !important;
            }
            .text-gray-500, .dark\:text-gray-400 {
                color: #6b7280 !important;
            }
            .text-gray-700, .dark\:text-gray-300 {
                color: #374151 !important;
            }
            .text-gray-600, .dark\:text-gray-400 {
                color: #4b5563 !important;
            }
        }
    </style>
</x-filament-panels::page>