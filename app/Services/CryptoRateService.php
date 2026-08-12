<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class CryptoRateService
{
    public function rates(): array
    {
        try {
            $data = Http::acceptJson()->timeout(6)->get(config('pulsanium.rates_url'))->throw()->json();
        } catch (\Throwable) {
            $data = [];
        }

        return [
            'vex' => $this->rate($data['vexanium']['idr'] ?? null, config('pulsanium.vex.fallback_rate_idr')),
            'eth' => $this->rate($data['ethereum']['idr'] ?? null, config('pulsanium.eth.fallback_rate_idr')),
        ];
    }

    private function rate(mixed $value, float $fallback): array
    {
        return is_numeric($value) && (float) $value > 0
            ? ['value' => (float) $value, 'source' => 'CoinGecko']
            : ['value' => $fallback, 'source' => 'fallback'];
    }
}
