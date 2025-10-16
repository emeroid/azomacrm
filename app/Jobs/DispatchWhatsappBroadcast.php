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

    protected string $sessionId;
    protected array $phoneNumbers;
    protected string $message;
    protected int $messageDelaySeconds;
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
        ?int $campaignId = null,
        ?int $scheduledMessageId = null,
        ?int $autoResponderLogId = null
    )
    {
        $this->sessionId = $sessionId;
        $this->phoneNumbers = $phoneNumbers;
        $this->message = $message;
        $this->messageDelaySeconds = $delaySeconds;
        $this->campaignId = $campaignId;
        $this->scheduledMessageId = $scheduledMessageId;
        $this->autoResponderLogId = $autoResponderLogId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $delayCounter = 0;

        foreach ($this->phoneNumbers as $number) {
            // Calculate the cumulative delay for this specific message
            $calculatedDelay = $this->messageDelaySeconds * $delayCounter;

            // Dispatch an individual job for each number with the calculated delay
            SendSingleWhatsappMessage::dispatch(
                $this->sessionId,
                $number,
                $this->message,
                $this->campaignId,
                $this->scheduledMessageId,
                $this->autoResponderLogId
            )->delay(now()->addSeconds($calculatedDelay));

            
            $delayCounter++;
        }
    }
}