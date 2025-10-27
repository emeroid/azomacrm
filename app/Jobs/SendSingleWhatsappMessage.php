<?php

namespace App\Jobs;

use App\Models\Campaign;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\MessageLog;
use App\Utils\SpintaxParser;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis; // **Import Redis**

class SendSingleWhatsappMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $sessionId;
    protected string $phoneNumber;
    protected string $spintaxMessage;
    protected ?string $mediaUrl;
    protected int $userId;
    protected ?int $campaignId;
    protected ?int $scheduledMessageId;
    protected ?int $autoResponderLogId;

    /**
     * Create a new job instance.
     */
    public function __construct(
        string $sessionId, 
        string $phoneNumber, 
        string $message, 
        ?string $mediaUrl,
        int $userId,
        ?int $campaignId,
        ?int $scheduledMessageId,
        ?int $autoResponderLogId
    )
    {
        $this->sessionId = $sessionId;
        $this->phoneNumber = $phoneNumber;
        $this->spintaxMessage = $message;
        $this->mediaUrl = $mediaUrl;
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
        if ($this->campaignId) {
            $campaign = Campaign::find($this->campaignId);

            // If the campaign is paused, re-queue this job for 5 minutes from now.
            // The job will run again and re-check the status.
            if ($campaign && $campaign->status === 'paused') {
                $this->release(300); // 300 seconds = 5 minutes
                return; // Stop execution
            }

            // If the campaign was cancelled, just fail the job silently.
            if ($campaign && $campaign->status === 'cancelled') {
                $this->fail(new \Exception('Campaign was cancelled.'));
                return; // Stop execution
            }
        }

        $finalMessage = SpintaxParser::parse($this->spintaxMessage);

        // Create a log entry
        $log = MessageLog::create([
            'user_id' => $this->userId,
            'message_id' => 'temp-' . \Illuminate\Support\Str::uuid(),
            'session_id' => $this->sessionId,
            'recipient_number' => $this->phoneNumber,
            'message' => $finalMessage, // **Log the final, parsed message**
            'media_url' => $this->mediaUrl, // **Make sure media_url is $fillable in MessageLog**
            'status' => 'queued',
            'sent_at' => null,
            'campaign_id' => $this->campaignId,
            'scheduled_message_id' => $this->scheduledMessageId,
            'auto_responder_log_id' => $this->autoResponderLogId,
        ]);

        try {
            // Prepare the payload for the Node.js worker
            $payload = json_encode([
                'sessionId' => $this->sessionId,
                'to' => $this->phoneNumber,
                'message' => $finalMessage, // **Send the final message**
                'mediaUrl' => $this->mediaUrl,
                'tempMessageId' => $log->message_id,
            ]);

            // Publish the job to the Redis list channel and finish.
            $queueName = config('database.redis.queues.default', 'whatsapp:send_queue');
            
            Redis::connection('pubsub')->lpush('whatsapp:send_queue', $payload);
            // Redis::lpush($queueName, $payload);

            Log::info("Successfully pushed to Redis List [{$queueName}]. Payload: {$payload}");

        } catch (\Exception $e) {
            Log::error("Failed to publish message to Redis for {$this->phoneNumber}: " . $e->getMessage());

            // If we can't even publish to Redis, mark the log as failed.
            $log->update(['status' => 'failed']);
            $this->updateParentFailureCount();

            // Re-throw the exception to let Laravel's queue system handle retries.
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