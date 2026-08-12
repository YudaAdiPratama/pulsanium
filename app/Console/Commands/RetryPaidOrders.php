<?php

namespace App\Console\Commands;

use App\Models\BarterOrder;
use App\Services\GowaService;
use Illuminate\Console\Command;

class RetryPaidOrders extends Command
{
    protected $signature = 'orders:retry-gowa {--limit=25 : Jumlah maksimum pesanan per eksekusi}';

    protected $description = 'Kirim ulang pesanan PAID yang belum diterima GOWA';

    public function handle(GowaService $gowa): int
    {
        $orders = BarterOrder::where('status', 'PAID')
            ->oldest()->limit(max(1, (int) $this->option('limit')))->get();

        foreach ($orders as $order) {
            $gowa->submit($order);
            $this->line("{$order->id}: {$order->fresh()->status}");
        }

        $this->info("{$orders->count()} pesanan diperiksa.");

        return self::SUCCESS;
    }
}
