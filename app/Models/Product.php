<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'base_price',
        'image_path',
        'is_active',
        'created_by', // admin who created the product
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    // Get current price for a specific marketer
    public function getPriceForMarketer($marketerId)
    {
        return $this->marketerPrices()
            ->where('marketer_id', $marketerId)
            ->value('price') ?? $this->base_price;
    }

    public static function boot() {
        parent::boot();

        static::creating(function ($product) {
            // $product->created_by = request()->user()->id;
        });
    }
}
