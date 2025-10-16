<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EmbeddableForm;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'form_id' => 'required|exists:embeddable_forms,id',
            'customer.full_name' => 'required|string|max:255',
            'customer.mobile' => 'required|string|max:20',
            'customer.address' => 'required|string|max:500',
            'customer.state' => 'required|string|max:100',
            'customer.email' => 'nullable|email|max:255',
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
            'products.*.price' => 'required'
        ]);
    
        try {
            DB::beginTransaction();
    
            $form = EmbeddableForm::with('products')->findOrFail($validated['form_id']);
            
            $order = new Order([
                'full_name' => $validated['customer']['full_name'],
                'email' => $validated['customer']['email'],
                'mobile' => $validated['customer']['mobile'],
                'address' => $validated['customer']['address'],
                'state' => $validated['customer']['state'],
                'marketer_id' => $form->marketer_id,
                'status' => Order::STATUS_PROCESSING
            ]);
            
            $order->save();
            
            $orderItems = [];
            $products = $form->products->keyBy('id');
            
            foreach ($validated['products'] as $productData) {

                $product = $products[$productData['product_id']] ?? null;
                
                if (!$product) {
                    throw new \Exception("Product not found in form's products");
                }
                
                $orderItems[] = new OrderItem([
                    'product_id' => $product->id,
                    'quantity' => $productData['quantity'],
                    'unit_price' => $productData['price'],
                ]);
            }
            
            $order->items()->saveMany($orderItems);
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Order created successfully',
                'order_id' => $order->id
            ], 201);
    
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Embeddable Form Order Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);
    
            return response()->json([
                'success' => false,
                'message' => 'Order creation failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : null,
                'TEST' => $request->input()
            ], 500);
        }
    }
    
    protected function calculateTotal($products, $form)
    {
        return collect($products)->sum(function ($product) use ($form) {
            if ($product['quantity'] > 0) {
                return $form->products->find($product['product_id'])->pivot->marketer_price * $product['quantity'];
            }
            return 0;
        });
    }
}
