<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\WhatsappDevice;
use Illuminate\Support\Facades\Log;

class DispatchWhatsappBroadcast implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // **NEW ANTI-BAN SETTINGS**
    private const CHUNK_SIZE = 10; // Safer chunk size
    private const BREAK_MINUTES_MIN = 20; // Minimum break of 20 minutes
    private const BREAK_MINUTES_MAX = 45; // Maximum break of 45 minutes
    
    // **NEW: Define human sleeping hours (based on your server's timezone)**
    // 23:00 (11 PM) to 8:00 (8 AM)
    private const SLEEP_START_HOUR = 23;
    private const SLEEP_END_HOUR = 8;

    protected string $sessionId;
    protected array $phoneNumbers;
    protected string $message;
    protected ?string $mediaUrl;
    protected int $userId; 
    protected ?int $campaignId;
    protected ?int $scheduledMessageId;
    protected ?int $autoResponderLogId;

    public function __construct(
        string $sessionId, 
        array $phoneNumbers, 
        string $message, 
        ?string $mediaUrl, // <-- Fixed type-hint (was missing nullable)
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

    public function handle(): void
    {
        // **NEW: Check for "Sleeping Hours"**
        // We use the timezone defined in your Laravel config (config/app.php)
        $now = now(config('app.timezone'));
        $hour = (int) $now->format('G'); // Get hour in 24-format (0-23)

        if ($hour >= self::SLEEP_START_HOUR || $hour < self::SLEEP_END_HOUR) {
            Log::info("Campaign [{$this->campaignId}] is sleeping. Pausing until 8:05 AM.");
            
            // Calculate wakeup time (e.g., 8:05 AM)
            $wakeupTime = $now->copy()->setTime(self::SLEEP_END_HOUR, 5, 0);
            
            // If wakeup time is in the past (e.g., it's 3 AM), set it for the same day
            // If it's 11 PM, it will correctly be set for tomorrow morning
            if ($wakeupTime < $now) {
                $wakeupTime->addDay();
            }

            // Re-dispatch this *same job* (with all remaining numbers) to run at wakeup time
            self::dispatch(
                $this->sessionId,
                $this->phoneNumbers,
                $this->message,
                $this->mediaUrl,
                $this->userId,
                $this->campaignId,
                $this->scheduledMessageId,
                $this->autoResponderLogId
            )->onQueue('whatsapp-broadcasts')->delay($wakeupTime);

            // Stop processing this job
            return;
        }

        // --- If we are awake, proceed as normal ---
        
        $device = WhatsappDevice::where('session_id', $this->sessionId)->first();
        
        $minDelay = $device->min_delay ?? 25;
        $maxDelay = $device->max_delay ?? 60;
        
        $currentChunk = array_slice($this->phoneNumbers, 0, self::CHUNK_SIZE);
        $remainingNumbers = array_slice($this->phoneNumbers, self::CHUNK_SIZE);

        $cumulativeDelay = 0;

        foreach ($currentChunk as $number) {
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

        if (!empty($remainingNumbers)) {
            // **NEW: Add "Jitter" to the break time**
            $randomBreakMinutes = rand(self::BREAK_MINUTES_MIN, self::BREAK_MINUTES_MAX);
            
            Log::info("Campaign [{$this->campaignId}] chunk finished. Taking a {$randomBreakMinutes} minute break.");

            self::dispatch(
                $this->sessionId,
                $remainingNumbers,
                $this->message,
                $this->mediaUrl,
                $this->userId,
                $this->campaignId,
                $this->scheduledMessageId,
                $this->autoResponderLogId
            )->onQueue('whatsapp-broadcasts')->delay(now()->addMinutes($randomBreakMinutes));
        } else {
            Log::info("Campaign [{$this->campaignId}] has completed.");
            // Optional: You could update the campaign status to 'completed' here
            // \App\Models\Campaign::find($this->campaignId)?->update(['status' => 'completed']);
        }
    }
}

// namespace App\Jobs;

// use Illuminate\Bus\Queueable;
// use Illuminate\Contracts\Queue\ShouldQueue;
// use Illuminate\Foundation\Bus\Dispatchable;
// use Illuminate\Queue\InteractsWithQueue;
// use Illuminate\Queue\SerializesModels;
// use App\Models\WhatsappDevice;

// class DispatchWhatsappBroadcast implements ShouldQueue
// {
//     use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

//     // **ANTI-BAN SETTINGS**
//     private const CHUNK_SIZE = 10; // Send 20 messages at a time
//     private const BREAK_MINUTES = 20; // Wait 15 minutes between chunks

//     protected string $sessionId;
//     protected array $phoneNumbers;
//     protected string $message;
//     protected ?string $mediaUrl;
//     protected int $userId; 
//     protected ?int $campaignId;
//     protected ?int $scheduledMessageId;
//     protected ?int $autoResponderLogId;

//     public function __construct(
//         string $sessionId, 
//         array $phoneNumbers, 
//         string $message, 
//         ?string $mediaUrl,
//         int $userId,
//         ?int $campaignId = null,
//         ?int $scheduledMessageId = null,
//         ?int $autoResponderLogId = null
//     )
//     {
//         $this->sessionId = $sessionId;
//         $this->phoneNumbers = $phoneNumbers;
//         $this->message = $message;
//         $this->mediaUrl = $mediaUrl;
//         $this->userId = $userId;
//         $this->campaignId = $campaignId;
//         $this->scheduledMessageId = $scheduledMessageId;
//         $this->autoResponderLogId = $autoResponderLogId;
//     }

//     public function handle(): void
//     {
//         $device = WhatsappDevice::where('session_id', $this->sessionId)->first();
        
//         // Use device's settings (20-60s) or fallback
//         $minDelay = $device->min_delay ?? 25;
//         $maxDelay = $device->max_delay ?? 60;
        
//         // **NEW RECURSIVE CHUNKING LOGIC**

//         // 1. Get the first chunk of numbers from the list
//         $currentChunk = array_slice($this->phoneNumbers, 0, self::CHUNK_SIZE);
        
//         // 2. Get the *rest* of the numbers
//         $remainingNumbers = array_slice($this->phoneNumbers, self::CHUNK_SIZE);

//         $cumulativeDelay = 0;

//         foreach ($currentChunk as $number) {
//             $randomDelay = rand($minDelay, $maxDelay);
//             $cumulativeDelay += $randomDelay;

//             SendSingleWhatsappMessage::dispatch(
//                 $this->sessionId,
//                 $number,
//                 $this->message, // Pass the *original* spintax message
//                 $this->mediaUrl,
//                 $this->userId,
//                 $this->campaignId,
//                 $this->scheduledMessageId,
//                 $this->autoResponderLogId
//             )->onQueue('whatsapp-broadcasts')->delay(now()->addSeconds($cumulativeDelay));
//         }

//         // 3. Check if there are any numbers left to send
//         if (!empty($remainingNumbers)) {
//             // Re-dispatch this job with the *remaining* numbers
//             // after a long break.
//             self::dispatch(
//                 $this->sessionId,
//                 $remainingNumbers, // <-- Pass the rest of the list
//                 $this->message,
//                 $this->mediaUrl,
//                 $this->userId,
//                 $this->campaignId,
//                 $this->scheduledMessageId,
//                 $this->autoResponderLogId
//             )->onQueue('whatsapp-broadcasts')->delay(now()->addMinutes(self::BREAK_MINUTES));
//         }
//     }
// }

// namespace App\Jobs;

// use Illuminate\Bus\Queueable;
// use Illuminate\Contracts\Queue\ShouldQueue;
// use Illuminate\Foundation\Bus\Dispatchable;
// use Illuminate\Queue\InteractsWithQueue;
// use Illuminate\Queue\SerializesModels;
// use App\Models\WhatsappDevice; // <-- Import the model

// class DispatchWhatsappBroadcast implements ShouldQueue
// {
//     use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

//     private const CHUNK_SIZE = 200;

//     protected string $sessionId;
//     protected array $phoneNumbers;
//     protected string $message;
//     protected ?string $mediaUrl;
//     protected int $userId; 
//     protected ?int $campaignId;
//     protected ?int $scheduledMessageId;
//     protected ?int $autoResponderLogId;

//     /**
//      * Create a new job instance.
//      * We remove $delaySeconds from here.
//      */
//     public function __construct(
//         string $sessionId, 
//         array $phoneNumbers, 
//         string $message, 
//         ?string $mediaUrl,
//         int $userId,
//         ?int $campaignId = null,
//         ?int $scheduledMessageId = null,
//         ?int $autoResponderLogId = null
//     )
//     {
//         $this->sessionId = $sessionId;
//         $this->phoneNumbers = $phoneNumbers;
//         $this->message = $message;
//         $this->mediaUrl = $mediaUrl;
//         $this->userId = $userId;
//         $this->campaignId = $campaignId;
//         $this->scheduledMessageId = $scheduledMessageId;
//         $this->autoResponderLogId = $autoResponderLogId;
//     }

//     /**
//      * Execute the job.
//      */
//     public function handle(): void
//     {
//         // **NEW: Fetch the device and its delay settings**
//         $device = WhatsappDevice::where('session_id', $this->sessionId)->first();

//         // Use device's settings or fallback to defaults
//         $minDelay = $device->min_delay ?? 10;  // Default 5s
//         $maxDelay = $device->max_delay ?? 60; // Default 15s

//         $chunks = array_chunk($this->phoneNumbers, self::CHUNK_SIZE);
//         $cumulativeDelay = 0;

//         foreach ($chunks as $chunk) {
//             foreach ($chunk as $number) {
//                 // **NEW: Calculate a random delay for this specific message**
//                 $randomDelay = rand($minDelay, $maxDelay);
//                 $cumulativeDelay += $randomDelay;

//                 SendSingleWhatsappMessage::dispatch(
//                     $this->sessionId,
//                     $number,
//                     $this->message,
//                     $this->mediaUrl,
//                     $this->userId,
//                     $this->campaignId,
//                     $this->scheduledMessageId,
//                     $this->autoResponderLogId
//                 )->onQueue('whatsapp-broadcasts')->delay(now()->addSeconds($cumulativeDelay));
//             }
//             sleep(3);
//         }
//     }
// }