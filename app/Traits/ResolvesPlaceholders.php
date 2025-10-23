<?php

namespace App\Traits;

use App\Models\Order;
use App\Models\FormSubmission;
use Illuminate\Support\Str;

trait ResolvesPlaceholders
{
    /**
     * Replaces placeholders (e.g., {customer_name}) in the message with actual data.
     *
     * @param string $message The original message string.
     * @return string The message with placeholders replaced.
     */
    public function resolveMessagePlaceholders(string $message): string
    {
        $entity = $this->getTargetEntity();

        if (!$entity) {
            // If the message targets ALL or STATUS/TEMPLATE (not a specific ID), 
            // we remove the placeholders to prevent sending {variable} text.
            return preg_replace('/\{([a-zA-Z0-9_]+)\}/', '', $message);
        }

        $replacements = $this->getPlaceholderReplacements($entity);
        
        // Use a regex to find all {key} patterns and replace them
        return preg_replace_callback('/\{([a-zA-Z0-9_]+)\}/', function ($matches) use ($replacements) {
            $key = $matches[1];
            return $replacements[strtolower($key)] ?? ''; // Default to empty string if not found
        }, $message);
    }

    /**
     * Gets an array of placeholder keys and their values for a given entity.
     *
     * @param object $entity
     * @return array
     */
    protected function getPlaceholderReplacements(object $entity): array
    {
        if ($entity instanceof Order) {
            return $this->getOrderReplacements($entity);
        }

        if ($entity instanceof FormSubmission) {
            return $this->getFormSubmissionReplacements($entity);
        }

        return [];
    }

    protected function getOrderReplacements(Order $order): array
    {
        // Map common placeholders to Order model attributes/accessors (lowercase keys for consistency)
        return [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'customer_name' => $order->full_name,
            'mobile' => $order->mobile,
            'phone' => $order->phone,
            'address' => $order->address,
            'state' => $order->state,
            'status' => Order::getStatuses()[$order->status] ?? $order->status,
            'total_amount' => number_format($order->total_price, 2), // Using accessor
            'product_name' => $order->product_name, // Using accessor
            'created_at' => $order->created_at->format('M d, Y'),
            // Add other relevant fields...
        ];
    }

    protected function getFormSubmissionReplacements(FormSubmission $submission): array
    {
        $replacements = [
            'submission_id' => $submission->id,
            'submitted_at' => $submission->submitted_at ? $submission->submitted_at->format('M d, Y') : 'N/A',
            'form_name' => $submission->template->name ?? 'Unknown Form',
            'fullname_with_mobile' => $submission->fullname_with_mobile, // Using accessor
            // Add other relevant fields...
        ];
        
        // Dynamically add all key/value pairs from the 'data' JSON column
        // e.g., 'data' => ['mobile' => '080...', 'name' => 'John'] -> {mobile}, {name}
        foreach ($submission->data as $key => $value) {
            // Ensure the key is a string for placeholder compatibility and the value is scalar
            if (is_string($key) && (is_string($value) || is_numeric($value))) {
                $replacements[strtolower($key)] = (string) $value;
            }
        }

        return $replacements;
    }
}