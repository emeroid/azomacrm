<?php

namespace App\Models;

use App\Services\OrderStatusLogger;
use App\Services\UpdateUsername;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class Order extends Model
{
    use HasFactory;

    const STATUS_PROCESSING = 'processing';
    const STATUS_IN_TRANSIT = 'in_transit';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_RETURNED = 'returned';
    const STATUS_SCHEDULED = 'scheduled';

    // new status for delivery agent
    const STATUS_NOT_READY = 'not_ready';
    const STATUS_NOT_INTERESTED = 'not_interested';
    const STATUS_NOT_REACHABLE = 'not_reachable';
    const STATUS_PHONE_SWITCHED_OFF = 'phone_switched_off';
    const STATUS_TRAVELLED = 'travelled';
    const STATUS_NOT_AVAILABLE = 'not_available';

    const SOURCE_TYPE_CUSTOMER = 'CUSTOMER';
    const SOURCE_TYPE_MARKETER = 'MARKETER';
    
    protected $fillable = [
        'order_number',
        'status',
        'notes',
        'marketer_id',
        'call_agent_id',
        'delivery_agent_id',
        'delivery_notes',
        'email', // nullable
        'mobile', // require
        'phone', //nullable
        'address', // required
        'state', // required
        'full_name', // required
        'source_type',
        'source_id',
    ];

        
    protected $casts = [
        'marketer_id' => 'integer',
        'call_agent_id' => 'integer',
        'delivery_agent_id' => 'integer'
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
    */
    protected $appends = ['product_name']; // Add your accessor name here

    protected $attributes = [
        'status' => self::STATUS_PROCESSING,
    ];

    public function items()
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }

    public function products()
    {
        return $this->hasManyThrough(
            Product::class,
            OrderItem::class,
            'order_id', // Foreign key on OrderItem table
            'id', // Foreign key on Product table
            'id', // Local key on Order table
            'product_id' // Local key on OrderItem table
        );
    }

    public function getProductNameAttribute() {
        $productNames = $this->products->pluck('name')->filter()->toArray();
        
        if (empty($productNames)) {
            return null;
        }
        
        if (count($productNames) === 1) {
            return $productNames[0];
        }
        
        return implode(' + ', $productNames);
    }

    public function getTotalPriceAttribute() {
        $amount = 0;
       foreach($this->items as $item) {
            $amount += $item->unit_price * $item->quantity;
       }

       return $amount;
    }

    public function communications(): HasMany
    {
        return $this->hasMany(OrderCommunication::class);
    }

    public function marketer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'marketer_id');
    }

    public function deliveryAgent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'delivery_agent_id');
    }

    // Status transition methods
    public function markAsInTransit(User $agent, $notes = null)
    {
        $this->update([
            'status' => self::STATUS_IN_TRANSIT,
            'delivery_agent_id' => $agent->id,
            'delivery_notes' => $notes,
        ]);
    }

    public function markAsDelivered($notes = null)
    {
        $this->update([
            'status' => self::STATUS_DELIVERED,
            'delivery_notes' => $notes,
        ]);
    }

    public function cancel($reason = null)
    {
        $this->update([
            'status' => self::STATUS_CANCELLED,
            'delivery_notes' => $reason,
        ]);
    }

    public function callAgent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'call_agent_id');
    }

    // Assignment method for call agents
    public function assignToCallAgent(User $agent)
    {
        $this->update(['call_agent_id' => $agent->id]);
    }

    // Reassignment method
    public function reassignCallAgent(User $newAgent)
    {
        $this->update(['call_agent_id' => $newAgent->id]);
    }

    public function formSubmit() {
        return $this->belongsTo(FormSubmission::class, 'source_id');
    }

    public function isOrderByCustomer() {
        return $this->source_type && $this->source_type === self::SOURCE_TYPE_CUSTOMER;
    }

    public static function getStatuses(): array
    {
        return [
            self::STATUS_PROCESSING => 'Processing',
            self::STATUS_IN_TRANSIT => 'In Transit',
            self::STATUS_CONFIRMED => 'Confirmed',
            self::STATUS_DELIVERED => 'Delivered',
            self::STATUS_CANCELLED => 'Cancelled',
            self::STATUS_RETURNED => 'Returned',
            self::STATUS_SCHEDULED => 'Scheduled',

            // Delivery agent-specific
            self::STATUS_NOT_READY => 'Not Ready',
            self::STATUS_NOT_INTERESTED => 'Not Interested',
            self::STATUS_NOT_REACHABLE => 'Not Reachable',
            self::STATUS_PHONE_SWITCHED_OFF => 'Phone Switched Off',
            self::STATUS_TRAVELLED => 'Travelled',
            self::STATUS_NOT_AVAILABLE => 'Not Available',
        ];
    }

    public function getSourceFormByNote() {
        $formNote =  explode('::', $this->formSubmit->data['products'])[2];

        if($this->isOrderByCustomer() && !empty($formNote)) {
            return $formNote ;
        }

        return false;
    }

    protected static function booted()
    {
        static::creating(function ($order) {
            
            if ($order->marketer_id) {
                
                $user = \App\Models\User::find($order->marketer_id);
                UpdateUsername::exec($user);
    
                if ($user && $user->username) {
                    $prefix = $user->username;
    
                    // Default order number
                    $orderNumber = $prefix . '-' . strtoupper(Str::random(6));
    
                    // Ensure uniqueness across all orders
                    while (self::where('order_number', $orderNumber)->exists()) {
                        $orderNumber = $prefix . '-' . strtoupper(Str::random(6));
                    }
    
                    $order->order_number = $orderNumber;
                }
            }
    
            if (empty($order->order_number)) {
                $order->order_number = 'ORD-' . strtoupper(Str::random(6));
            }
        });
    }

    /**
     * Alias for full_name to match common placeholder naming
     */
    public function getCustomerNameAttribute() 
    {
        return $this->full_name;
    }
}
