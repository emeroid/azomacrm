<?php

namespace App\Console\Commands;

use App\Models\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class UpdateScheduledOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:update-scheduled';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates scheduled orders to processing status if their date is today or in the past.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $today = Carbon::today();

        $updatedOrdersCount = Order::where('status', Order::STATUS_SCHEDULED)
            ->whereDate('scheduled_at', '<=', $today)
            ->update([
                'status' => Order::STATUS_PROCESSING, 
                'updated_at' => now()
            ]);

        $this->info("Successfully updated $updatedOrdersCount scheduled orders to processing.");

        return 0;
    }
}