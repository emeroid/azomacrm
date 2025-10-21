<?php

namespace App\Console\Commands;

use App\Jobs\DispatchWhatsappBroadcast;
use App\Models\FormSubmission;
use App\Models\Order;
use App\Models\ScheduledMessage;
use Illuminate\Console\Command;

class ProcessScheduledMessages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'whatsapp:process-scheduled';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send reminders for abandoned carts';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $messages = ScheduledMessage::where('send_at', '<=', now())
                                    ->whereNull('sent_at')
                                    ->get();

        foreach ($messages as $message) {
            
            $potentialRecipients = [];
            
            if ($message->action_type === Order::class) {
                $query = Order::query();
                $criteria = $message->target_criteria;

                if ($criteria['type'] === 'STATUS') {
                    $query->where('status', $criteria['status']);
                }
                
                $orders = $query->get();
                
                foreach ($orders as $order) {
                    // 💡 NEW: Check both fields, prioritizing 'mobile' (or based on business logic)
                    $potentialRecipients[] = $order->mobile;
                    $potentialRecipients[] = $order->phone;
                }
            }

            // --- Logic for FORM_SUBMISSION ---
            if ($message->action_type === FormSubmission::class) {
                $criteria = $message->target_criteria;
                
                $submissions = FormSubmission::where('status', FormSubmission::STATUS_ABANDONED)
                                                        ->where('form_template_id', $criteria['form_template_id'])
                                                        ->get();
                                                        
                foreach ($submissions as $submission) {
                    $number = $submission->getWhatsappNumber($message->whatsapp_field_name);
                    if ($number) {
                        $potentialRecipients[] = $number;
                    }
                }
            }

            // 1. Clean and unique the list of potential recipients
            $recipients = array_unique(array_filter($potentialRecipients, function($number) {
                // Basic cleanup/validation (optional, but good practice)
                return $number && preg_match('/^\+?\d{7,15}$/', $number);
            }));

            // 2. Dispatch Sending Job
            if (!empty($recipients) && $message->whatsapp_device_id) {
                
                // CRUCIAL: The SendWhatsappBroadcast job (or a service it uses)
                // MUST contain logic to hit your gateway's "check number" API
                // before actually sending, to prevent wasting credits/time on invalid numbers.
                $delaySeconds = 30;
                \App\Jobs\DispatchWhatsappBroadcast::dispatch(
                    $message->device->session_id,
                    $recipients, // Send the list of potential 
                    $message->message,
                    $delaySeconds,              // 4th Arg: delaySeconds (Required)
                    null,                       // 5th Arg: campaignId (Not Applicable)
                    $message->id,               // 6th Arg: scheduledMessageId (CRUCIAL)
                    null

                )->onQueue('whatsapp-broadcasts');
            }

            $message->update(['sent_at' => now()]);
        }
    }
}
