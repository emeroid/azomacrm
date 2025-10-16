<?php

namespace App\Console\Commands;

use App\Models\ScheduledMessage;
use Illuminate\Console\Command;

class SendScheduledFollowups extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'followups:send';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send scheduled follow-up messages';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $messages = ScheduledMessage::where('send_at', '<=', now())
        ->whereNull('sent_at')
        ->get();

        foreach ($messages as $msg) {
            SendWhatsappMessage::dispatch($msg->session_id, $msg->recipient_number, $msg->message);
            $msg->update(['sent_at' => now()]);
        }
        $this->info(count($messages) . ' scheduled follow-ups have been queued.');
    }
}
