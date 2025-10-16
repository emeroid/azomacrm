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
        ?int $campaignId,
        ?int $scheduledMessageId,
        ?int $autoResponderLogId
    )
    {
        $this->sessionId = $sessionId;
        $this->phoneNumber = $phoneNumber;
        $this->message = $message;
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
        $userId = auth()->id() ?? MessageLog::getUserFromSessionId($this->sessionId);

        try {
            $response = Http::timeout(self::HTTP_TIMEOUT_SECONDS)
                 ->post(config('services.whatsapp.gateway_url') . '/messages/send', [
                    'sessionId' => $this->sessionId,
                    'to' => $this->phoneNumber,
                    'message' => $this->message,
            ]);

            $data = $response->json();

            if ($response->successful() && isset($data['success']) && $data['success'] && isset($data['id'])) {
                MessageLog::create([
                    'user_id' => $userId,
                    'message_id' => $data['id'],
                    'session_id' => $this->sessionId,
                    'recipient_number' => $this->phoneNumber,
                    'message' => $this->message,
                    'status' => 'queued',
                    'sent_at' => null,
                    'campaign_id' => $this->campaignId,
                    'scheduled_message_id' => $this->scheduledMessageId,
                    'auto_responder_log_id' => $this->autoResponderLogId,
                ]);
                Log::info("Message logged successfully for {$this->phoneNumber}. ID: {$data['id']}");
            } else {
                $errorDetails = $data['error'] ?? 'Unknown gateway error.';
                Log::error("Failed to send message to {$this->phoneNumber} via gateway: {$errorDetails}");
                $this->createFailedMessageLog();
                $this->updateParentFailureCount();
            }

        } catch (\Exception $e) {
            Log::error("HTTP connection failed to gateway for {$this->phoneNumber}: " . $e->getMessage());
            $this->createFailedMessageLog();
            $this->updateParentFailureCount();
        }
    }

    /**
     * Creates a MessageLog entry for a failed send attempt.
     */
    private function createFailedMessageLog(): void
    {
        $userId = auth()->id() ?? MessageLog::getUserFromSessionId($this->sessionId);
        MessageLog::create([
            'user_id' => $userId,
            'message_id' => \Illuminate\Support\Str::uuid(),
            'session_id' => $this->sessionId,
            'recipient_number' => $this->phoneNumber,
            'message' => $this->message,
            'status' => 'failed',
            'sent_at' => now(),
            'campaign_id' => $this->campaignId,
            'scheduled_message_id' => $this->scheduledMessageId,
            'auto_responder_log_id' => $this->autoResponderLogId,
        ]);
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