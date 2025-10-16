<div class="space-y-4">
    @foreach($field->properties['products'] as $product)
        <div class="flex items-center justify-between p-3 border border-gray-200 rounded-md">
            <div class="flex items-center space-x-4">
                <input type="radio" 
                       name="product_id" 
                       id="product_{{ $product['product'] }}" 
                       value="{{ $product['product'] }}"
                       class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300">
                <label for="product_{{ $product['product'] }}" class="block text-sm font-medium text-gray-700">
                    {{ $product['note'] }} - ₦{{  number_format($product['price'], 2) }}
                </label>
            </div>
        </div>
    @endforeach
</div>