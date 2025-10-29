<?php

namespace App\Console\Commands;

use App\Jobs\SendSingleWhatsappMessage;
use App\Models\FormSubmission;
use App\Models\Order;
use App\Models\ScheduledMessage;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Http;

class ProcessScheduledMessages extends Command
{
    protected $signature = 'whatsapp:process-scheduled';
    protected $description = 'Processes and sends scheduled WhatsApp messages with dynamic data.';

    public function handle()
    {
        $messages = ScheduledMessage::where('send_at', '<=', now())
                                    ->whereNull('sent_at')
                                    ->get();

        foreach ($messages as $message) {
            
            $message->update(['sent_at' => now()]); 

            $device = $message->device;
            if (!$device) {
                $this->error("No device found for scheduled message ID: {$message->id}");
                continue;
            }
            
            // 1. Warm-up WhatsApp session
            Http::withHeaders(['X-API-KEY' => config('services.whatsapp.api_key')])
                ->post(config('services.whatsapp.gateway_url') . '/sessions/start', [
                    'sessionId' => $device->session_id,
                ]);

            $targets = $this->getTargetModels($message);
            if ($targets->isEmpty()) {
                $this->warn("No targets found for scheduled message ID: {$message->id}");
                continue;
            }

            // **HUMAN-LIKE DELAY LOGIC (Borrowed from your anti-ban job)**
            $minDelay = $device->min_delay ?? 25;
            $maxDelay = $device->max_delay ?? 60;
            $cumulativeDelay = 0;
            
            $this->info("Processing {$targets->count()} targets for message ID: {$message->id} with human-like delays...");
            
            foreach ($targets as $target) {
                $recipientNumber = $this->getRecipientNumber($message, $target);
                if (!$recipientNumber) {
                    $this->warn("Skipping target ID {$target->id} - no phone number.");
                    continue; 
                }

                $resolvedMessage = $this->resolvePlaceholders($message->message, $target);

                // **NEW: Apply a random, cumulative delay to each job**
                $randomDelay = rand($minDelay, $maxDelay);
                $cumulativeDelay += $randomDelay;

                SendSingleWhatsappMessage::dispatch(
                    $device->session_id,
                    $recipientNumber, 
                    $resolvedMessage,
                    $message->media_url ?? null, // <-- FIX: Use null, not '', for ?string
                    $device->user_id,
                    null,                       
                    $message->id,               
                    null
                )->onQueue('whatsapp-broadcasts')->delay(now()->addSeconds($cumulativeDelay));
            }

            $this->info("All jobs for message ID: {$message->id} have been dispatched.");
        }
    }
    
    
    /**
     * Retrieves the target models (Orders or Submissions) based on criteria.
     * @param ScheduledMessage $message
     * @return Collection
     */
    protected function getTargetModels(ScheduledMessage $message): Collection
    {
        $criteria = $message->target_criteria;

        if ($message->action_type === Order::class) {
            $query = Order::query();
            
            if ($criteria['type'] === 'ALL') {
                return $query->get();
            }
            
            if ($criteria['type'] === 'STATUS') {
                return $query->where('status', $criteria['status'])->get();
            }
            
            if ($criteria['type'] === 'SPECIFIC_ORDER' && isset($criteria['target_id'])) {
                 // Important: use find() which returns one model, but put it in a collection
                 $order = $query->find($criteria['target_id']);
                 return $order ? Collection::make([$order]) : Collection::make([]);
            }
        }

        if ($message->action_type === FormSubmission::class) {
            $query = FormSubmission::where('form_template_id', $criteria['form_template_id']);

             // Logic from original code seems to target abandoned carts, but let's expand for general criteria
             // If type is ALL, it sends to ALL submissions for that template
            if ($criteria['type'] === 'ALL') {
                return $query->get();
            }
            
            if ($criteria['type'] === 'SPECIFIC_SUBMISSION' && isset($criteria['target_id'])) {
                 $submission = $query->find($criteria['target_id']);
                 return $submission ? Collection::make([$submission]) : Collection::make([]);
            }
        }
        
        return Collection::make([]);
    }

    /**
     * Resolves dynamic placeholders in the message content using the target model data.
     * @param string $message
     * @param mixed $target
     * @return string
     */
    protected function resolvePlaceholders(string $message, $target): string
    {
        // Default data map (only works if target is an Eloquent model)
        $dataMap = $target->toArray();

        // Specific handling for common keys in Orders
        if ($target instanceof Order) {
            $dataMap['customer_name'] = $target->full_name; // Assuming 'full_name' exists
            $dataMap['order_number'] = $target->order_number;
            $dataMap['total'] = number_format($target->total, 2); 
            // Add any other specific Order placeholders you use here
        }

        // Specific handling for Form Submissions (data is often a JSON column)
        if ($target instanceof FormSubmission) {
            // Merge the JSON data for field access
            $dataMap = array_merge($dataMap, json_decode($target->data, true) ?? []);
            // Add any specific FormSubmission placeholders here
            $dataMap['submission_id'] = $target->id;
            $dataMap['customer_name'] = $target->submitter_name_hint ?? 'Customer';
        }
        
        // Final substitution loop
        foreach ($dataMap as $key => $value) {
            $placeholder = '{' . $key . '}';
            
            // Ensure values are strings for str_replace
            $stringValue = is_array($value) ? json_encode($value) : (string) $value;
            
            $message = str_replace($placeholder, $stringValue, $message);
        }

        return $message;
    }
    
    /**
     * Determines the final recipient number from the target model.
     * @param ScheduledMessage $message
     * @param mixed $target
     * @return string|null
     */
    protected function getRecipientNumber(ScheduledMessage $message, $target): ?string
    {
        if ($target instanceof Order) {
            // Prioritize 'mobile' or fall back to 'phone'
            return $target->mobile ?: $target->phone; 
        }

        if ($target instanceof FormSubmission) {
            // Must use the field name specified in the ScheduledMessage
            return $target->getWhatsappNumber($message->whatsapp_field_name);
        }

        return null;
    }
}
