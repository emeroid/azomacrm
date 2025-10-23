<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\WhatsappDevice; // <-- Import the model

class DispatchWhatsappBroadcast implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private const CHUNK_SIZE = 200;

    protected string $sessionId;
    protected array $phoneNumbers;
    protected string $message;
    protected ?string $mediaUrl;
    protected int $userId; 
    protected ?int $campaignId;
    protected ?int $scheduledMessageId;
    protected ?int $autoResponderLogId;

    /**
     * Create a new job instance.
     * We remove $delaySeconds from here.
     */
    public function __construct(
        string $sessionId, 
        array $phoneNumbers, 
        string $message, 
        string $mediaUrl,
        int $userId,
        ?int $campaignId = null,
        ?int $scheduledMessageId = null,
        ?int $autoResponderLogId = null
    )
    {
        $this->sessionId = $sessionId;
        $this->phoneNumbers = $phoneNumbers;
        $this->message = $message;
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
        // **NEW: Fetch the device and its delay settings**
        $device = WhatsappDevice::where('session_id', $this->sessionId)->first();

        // Use device's settings or fallback to defaults
        $minDelay = $device->min_delay ?? 10;  // Default 5s
        $maxDelay = $device->max_delay ?? 60; // Default 15s

        $chunks = array_chunk($this->phoneNumbers, self::CHUNK_SIZE);
        $cumulativeDelay = 0;

        foreach ($chunks as $chunk) {
            foreach ($chunk as $number) {
                // **NEW: Calculate a random delay for this specific message**
                $randomDelay = rand($minDelay, $maxDelay);
                $cumulativeDelay += $randomDelay;

                SendSingleWhatsappMessage::dispatch(
                    $this->sessionId,
                    $number,
                    $this->message,
                    $this->mediaUrl,
                    $this->userId,
                    $this->campaignId,
                    $this->scheduledMessageId,
                    $this->autoResponderLogId
                )->onQueue('whatsapp-broadcasts')->delay(now()->addSeconds($cumulativeDelay));
            }
            sleep(3);
        }
    }
}