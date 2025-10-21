<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DispatchWhatsappBroadcast implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // A reasonable chunk size to avoid overwhelming the queue driver
    private const CHUNK_SIZE = 200;

    protected string $sessionId;
    protected array $phoneNumbers;
    protected string $message;
    protected int $messageDelaySeconds;
    protected int $userId; // Pass user ID for ownership
    protected ?int $campaignId;
    protected ?int $scheduledMessageId;
    protected ?int $autoResponderLogId;

    /**
     * Create a new job instance.
     */
    public function __construct(
        string $sessionId, 
        array $phoneNumbers, 
        string $message, 
        int $delaySeconds,
        int $userId,
        ?int $campaignId = null,
        ?int $scheduledMessageId = null,
        ?int $autoResponderLogId = null
    )
    {
        $this->sessionId = $sessionId;
        $this->phoneNumbers = $phoneNumbers;
        $this->message = $message;
        $this->messageDelaySeconds = $delaySeconds;
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
        // Split the total numbers into manageable chunks
        $chunks = array_chunk($this->phoneNumbers, self::CHUNK_SIZE);
        $cumulativeDelay = 0;

        foreach ($chunks as $chunk) {
            foreach ($chunk as $number) {
                // Dispatch an individual job for each number within the chunk
                SendSingleWhatsappMessage::dispatch(
                    $this->sessionId,
                    $number,
                    $this->message,
                    $this->userId,
                    $this->campaignId,
                    $this->scheduledMessageId,
                    $this->autoResponderLogId
                )->onQueue('whatsapp-broadcasts')->delay(now()->addSeconds($cumulativeDelay));

                // The delay only increments after dispatching a job
                $cumulativeDelay += $this->messageDelaySeconds;
            }

            // Optional: Add a small sleep between chunks to further ease pressure
            // on the gateway if you're sending very rapidly.
            sleep(1);
        }
    }
}