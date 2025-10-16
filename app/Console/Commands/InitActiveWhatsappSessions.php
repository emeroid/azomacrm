<?php

namespace App\Console\Commands;

use App\Models\WhatsappDevice;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class InitActiveWhatsappSessions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'whatsapp:init-active';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Initializes (loads) all critical WhatsApp sessions in the Node.js gateway.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $gatewayUrl = config('services.whatsapp.gateway_url');
        if (empty($gatewayUrl)) {
            $this->error('The whatsapp.gateway_url configuration is missing.');
            return Command::FAILURE;
        }

        // 1. Define which devices are "Active"
        // We assume 'connected' devices that aren't marked for deletion should be re-initialized.
        // You might add a boolean column like 'is_critical' to filter further.
        $criticalDevices = WhatsappDevice::where('status', 'connected')
                                         ->whereNotNull('session_id')
                                         ->get();

        $this->info("Attempting to initialize {$criticalDevices->count()} active sessions...");
        
        $startTime = microtime(true);
        $initializedCount = 0;

        foreach ($criticalDevices as $device) {
            $sessionId = $device->session_id;
            $this->line(" -> Sending start request for session: {$sessionId}");

            try {
                // Hitting the POST /sessions/start endpoint will force the Node.js gateway
                // to spin up the Puppeteer instance for this specific session.
                Http::timeout(10)->post("{$gatewayUrl}/sessions/start", [
                    'sessionId' => $sessionId,
                ])->throw(); // Use throw() to handle HTTP errors

                $initializedCount++;
                
            } catch (\Exception $e) {
                Log::error("Failed to start session {$sessionId}:", ['message' => $e->getMessage()]);
                $this->error("   Failed for {$sessionId}. Check gateway logs.");
            }
        }

        $elapsedTime = round(microtime(true) - $startTime, 2);
        $this->info("Successfully initialized {$initializedCount} sessions in {$elapsedTime} seconds.");

        return Command::SUCCESS;
    }
}