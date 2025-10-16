<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class FormSubmission extends Model
{
    // Status constants
    const STATUS_DRAFT = 'draft';
    const STATUS_SUBMITTED = 'submitted';
    const STATUS_ABANDONED = 'abandoned';

    public $fillable = [
        'form_template_id', 
        'data', 
        'ip_address', 
        'user_agent',
        'status',
        'abandoned_at',
        'submitted_at',
        'session_id'
    ];

    protected $casts =[
        'data' => 'array',
        'abandoned_at' => 'datetime',
        'submitted_at' => 'datetime',
    ];
    
    
    public function template() {
        return $this->belongsTo(FormTemplate::class, 'form_template_id');
    }

    public function getUserIdAttribute() {
        return $this->template->user_id;
    }

    public function lead() {
        return $this->hasOne(Order::class, 'source_id');
    }

    // Helper methods
    public function markAsSubmitted()
    {
        $this->update([
            'status' => self::STATUS_SUBMITTED,
            'submitted_at' => now()
        ]);
    }
    
    public function markAsAbandoned()
    {
        $this->update([
            'status' => self::STATUS_ABANDONED,
            'abandoned_at' => now()
        ]);
    }

    // Combine fullname + mobile
    public function getFullnameWithMobileAttribute(): string
    {
        $fullname = $this->data['fullname'] ?? '';
        $mobile   = $this->data['mobile'] ?? '';

        $userName = empty($fullname) ? "" : "{$fullname}";
        $userMobile = empty($fullname) ? "" : "({$mobile})";

        return trim($userName . " " . $userMobile);
    }

    // Combine state + address
    public function getAddressWithStateAttribute(): string
    {
        $address = $this->data['address'] ?? '';
        $state   = $this->data['state'] ?? '';

        $userAddress = empty($address) ? "" : "{$address},";
        $userState = empty($state) ? "" : " {$state}";

        return trim($userAddress . $userState);
    }

    // Parse product string
    public function getParsedProductsAttribute(): string
    {
        if (empty($this->data['products'])) {
            return '';
        }

        $parts = explode('::', $this->data['products']);
        if (count($parts) < 3) {
            // return $this->data['products']; // fallback
            [$productId, $price] = $parts;
            $productName = optional(\App\Models\Product::find($productId))->name ?? "Product #{$productId}";
            return trim("{$productName} - ₦" . number_format((float)$price, 2));
        }

        [$productId, $price, $note] = $parts;

        $productName = optional(\App\Models\Product::find($productId))->name ?? "Product #{$productId}";

        return trim("{$productName} - ₦" . number_format((float)$price, 2) . " ({$note})");
    }

    public function getWhatsappNumber(string $fieldName): ?string
    {
        // The $fieldName should be the 'name' attribute from TemplateField (e.g., 'mobile')
        $number = $this->data[$fieldName] ?? null;

        if ($number && preg_match('/^\+?\d{7,15}$/', $number)) {
            // Simple validation to ensure it looks like a phone number
            return preg_replace('/[^\d+]/', '', $number); // Clean the number
        }

        return null;
    }
}
