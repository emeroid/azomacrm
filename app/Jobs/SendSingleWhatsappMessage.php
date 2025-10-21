<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use App\Models\MessageLog;
use Illuminate\Support\Facades\Log;

class SendSingleWhatsappMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $sessionId;
    protected string $phoneNumber;
    protected string $message;
    protected int $userId;
    protected ?int $campaignId;
    protected ?int $scheduledMessageId;
    protected ?int $autoResponderLogId;

    private const HTTP_TIMEOUT_SECONDS = 90;

    /**
     * Create a new job instance.
     */
    public function __construct(
        string $sessionId, 
        string $phoneNumber, 
        string $message, 
        int $userId,
        ?int $campaignId,
        ?int $scheduledMessageId,
        ?int $autoResponderLogId
    )
    {
        $this->sessionId = $sessionId;
        $this->phoneNumber = $phoneNumber;
        $this->message = $message;
        $this->userId = $userId;
        $this->campaignId = $campaignId;
        $this->scheduledMessageId = $scheduledMessageId;
        $this->autoResponderLogId = $autoResponderLogId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // For simplicity, we assume we can infer the user ID or that it's handled upstream.
        $log = MessageLog::create([
                'user_id' => $this->userId,
                'message_id' => 'temp-' . \Illuminate\Support\Str::uuid(),
                'session_id' => $this->sessionId,
                'recipient_number' => $this->phoneNumber,
                'message' => $this->message,
                'status' => 'processing',
                'sent_at' => null,
                'campaign_id' => $this->campaignId,
                'scheduled_message_id' => $this->scheduledMessageId,
                'auto_responder_log_id' => $this->autoResponderLogId,
            ]);

        try {

            $response = Http::timeout(90)
                // NEW: Add the required API Key for security
                ->withHeaders(['X-API-KEY' => config('services.whatsapp.api_key')])
                ->post(config('services.whatsapp.gateway_url') . '/messages/send', [
                    'sessionId' => $this->sessionId,
                    'to' => $this->phoneNumber,
                    'message' => $this->message,
                ]);

            $data = $response->json();

            if ($response->successful() && $data['success']) {
                // STEP 2: Update the log with the real message ID from the gateway.
                // The status is 'queued' because the gateway has accepted it.
                // The webhook will later update it to 'sent', 'delivered', etc.
                $log->update([
                    'message_id' => $data['id'],
                    'status' => 'queued',
                ]);
            } else {
                // Throw an exception to trigger Laravel's retry mechanism
                throw new \Exception('Gateway failed to send message: ' . ($data['error'] ?? 'Unknown error'));
            }

        } catch (\Exception $e) {
            Log::error("HTTP connection failed to gateway for {$this->phoneNumber}: " . $e->getMessage());

            // STEP 3: If all retries fail, this block will be executed.
            $log->update(['status' => 'failed']);
            $this->updateParentFailureCount();

            // Re-throw the exception to mark the job as officially failed.
            $this->fail($e);
        }
    }
    
    /**
     * Helper to update the failure counter on the parent entity.
     */
    private function updateParentFailureCount(): void
    {
        if ($this->campaignId) {
            \App\Models\Campaign::find($this->campaignId)?->increment('failed_count');
        } elseif ($this->scheduledMessageId) {
            \App\Models\ScheduledMessage::find($this->scheduledMessageId)?->increment('failed_count');
        }
    }
}