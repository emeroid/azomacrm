<?php

namespace Database\Seeders;

use App\Models\FormTemplate;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\OrderCommunication;
use App\Models\Product;
use App\Models\OrderItem;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
    */
    public function run(): void
    {
        User::factory(2)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        // Create a processing order with 3 items
        // $order = Order::factory()
        // ->withMarketer(User::factory()->marketer())
        // ->withCallAgent(User::factory()->callAgent())
        // ->has(OrderItem::factory()->count(3), 'items')
        // ->create();

        // // Create a delivered order with specific marketer
        // $marketer = User::factory()->marketer()->create();
        // $order = Order::factory()
        // ->delivered()
        // ->withMarketer($marketer)
        // ->create();

        // // Create an order with communications
        // $order = Order::factory()
        // ->has(OrderCommunication::factory()->count(2), 'communications')
        // ->create();

        // // Create a product with specific price
        // $product = Product::factory()
        // ->state(['base_price' => 99.99])
        // ->create();



            // Create Product Type Template
            // $productTemplate = FormTemplate::create([
            //     'name' => 'Product Type',
            //     'slug' => 'product-type',
            //     'description' => 'Template for product order forms'
            // ]);
    
            // // Add required fields for product template
            // $fields = [
            //     [
            //         'name' => 'fullname',
            //         'label' => 'Full Name',
            //         'type' => 'text',
            //         'is_required' => true,
            //         'properties' => ['placeholder' => 'Enter your full name'],
            //         'order' => 1
            //     ],
            //     [
            //         'name' => 'mobile',
            //         'label' => 'Mobile Number',
            //         'type' => 'tel',
            //         'is_required' => true,
            //         'properties' => ['placeholder' => 'Enter your mobile number'],
            //         'order' => 2
            //     ],
            //     [
            //         'name' => 'address',
            //         'label' => 'Address',
            //         'type' => 'textarea',
            //         'is_required' => true,
            //         'properties' => ['rows' => 3, 'placeholder' => 'Enter your complete address'],
            //         'order' => 3
            //     ],
            //     [
            //         'name' => 'state',
            //         'label' => 'State',
            //         'type' => 'select',
            //         'is_required' => true,
            //         'properties' => [
            //             'options' => [
            //                 ['value' => 'AL', 'label' => 'Alabama'],
            //                 ['value' => 'AK', 'label' => 'Alaska'],
            //                 ['value' => 'AZ', 'label' => 'Arizona'],
            //                 ['value' => 'AR', 'label' => 'Arkansas'],
            //                 ['value' => 'CA', 'label' => 'California'],
            //             ]
            //         ],
            //         'order' => 4
            //     ],
            //     [
            //         'name' => 'products',
            //         'label' => 'Products',
            //         'type' => 'product_selector',
            //         'is_required' => true,
            //         'properties' => [
            //             'min_items' => 1,
            //             'allow_notes' => true,
            //             'allow_quantity' => true
            //         ],
            //         'order' => 5
            //     ]
            // ];
    
            // foreach ($fields as $field) {
            //     $productTemplate->fields()->create($field);
            // }
    
            // // Create a generic contact form template
            // $contactTemplate = FormTemplate::create([
            //     'name' => 'Contact Form',
            //     'slug' => 'contact-form',
            //     'description' => 'Basic contact information form'
            // ]);
    
            // $contactFields = [
            //     [
            //         'name' => 'name',
            //         'label' => 'Your Name',
            //         'type' => 'text',
            //         'is_required' => true,
            //         'properties' => ['placeholder' => 'Enter your name'],
            //         'order' => 1
            //     ],
            //     [
            //         'name' => 'email',
            //         'label' => 'Email Address',
            //         'type' => 'email',
            //         'is_required' => true,
            //         'properties' => ['placeholder' => 'Enter your email'],
            //         'order' => 2
            //     ],
            //     [
            //         'name' => 'message',
            //         'label' => 'Message',
            //         'type' => 'textarea',
            //         'is_required' => true,
            //         'properties' => ['rows' => 5, 'placeholder' => 'Enter your message'],
            //         'order' => 3
            //     ]
            // ];
    
            // foreach ($contactFields as $field) {
            //     $contactTemplate->fields()->create($field);
            // }
        
    }
}
