<?php

namespace App\Services;

use App\Models\BarterOrder;
use Illuminate\Support\Facades\Http;

class GowaService
{
    public function submit(BarterOrder $order): void
    {
        $message = sprintf('%s.%s.%s', $order->product_code, $order->phone, config('pulsanium.gowa.pin'));
        try {
            $response = Http::withHeaders(['X-Device-Id' => config('pulsanium.gowa.device_id')])
                ->timeout(15)->post(config('pulsanium.gowa.url'), [
                    'phone' => config('pulsanium.gowa.target_phone'), 'message' => $message,
                ]);
            $body = $response->json();
            $success = $response->successful() && strtoupper((string) data_get($body, 'code')) === 'SUCCESS';
            $order->update([
                'status' => $success ? 'SENT' : 'PAID',
                'gowa_message' => $message,
                'gowa_response' => ['http_status' => $response->status(), 'body' => $body],
                'last_error' => $success ? null : 'GOWA tidak mengembalikan status SUCCESS.',
                'sent_at' => $success ? now() : null,
            ]);
        } catch (\Throwable $e) {
            $order->update(['gowa_message' => $message, 'last_error' => $e->getMessage()]);
        }
    }
}
