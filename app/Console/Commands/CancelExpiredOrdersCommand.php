<?php

namespace App\Console\Commands;

use App\Models\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CancelExpiredOrdersCommand extends Command
{
    protected $signature = 'orders:cancel-expired {--hours=24 : Hours after which pending orders are cancelled}';
    protected $description = 'Auto-cancel unpaid orders after a specified number of hours';

    public function handle()
    {
        $hours = (int) $this->option('hours');
        $cutoff = now()->subHours($hours);

        $orders = Order::where('payment_status', 'pending')
            ->where('order_status', 'pending')
            ->where('created_at', '<', $cutoff)
            ->get();

        $count = 0;
        foreach ($orders as $order) {
            $order->update([
                'payment_status' => 'failed',
                'order_status'   => 'cancelled',
            ]);
            $count++;
        }

        $this->info("Cancelled {$count} expired orders older than {$hours} hours.");
        Log::info("Auto-cancelled {$count} expired orders older than {$hours} hours.");

        return self::SUCCESS;
    }
}
