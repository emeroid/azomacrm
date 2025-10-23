<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\ResolvesPlaceholders; // <--- NEW TRAIT

class ScheduledMessage extends Model
{
    use ResolvesPlaceholders;

    protected $fillable = [
        'user_id',
        'whatsapp_device_id',
        'action_type',
        'action_id',
        'target_criteria',
        'whatsapp_field_name',
        'message',
        'media_url',
        'sent_count',
        'failed_count',
        'send_at',
        'sent_at',
    ];


    protected $casts = [
        'target_criteria' => 'array'
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function device() {
        return $this->belongsTo(WhatsappDevice::class, 'whatsapp_device_id');
    }
    
    /**
     * Get the target entity (Order or FormSubmission) based on action_type and criteria.
     * This is crucial for the placeholder logic in the trait.
     */
    public function getTargetEntity()
    {
        // Only return an entity if it targets a SPECIFIC item
        if ($this->action_type === Order::class && isset($this->target_criteria['order_id'])) {
            return Order::find($this->target_criteria['order_id']);
        }
        
        if ($this->action_type === FormSubmission::class && isset($this->target_criteria['form_submission_id'])) {
            return FormSubmission::find($this->target_criteria['form_submission_id']);
        }

        return null;
    }
}
