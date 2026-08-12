<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class EthereumPaymentVerifier
{
    public function verify(string $txHash, string $wallet, string $expectedEth): array
    {
        $transaction = $this->rpc('eth_getTransactionByHash', [$txHash]);
        $receipt = $this->rpc('eth_getTransactionReceipt', [$txHash]);

        if (! is_array($transaction) || ! is_array($receipt)) {
            throw new RuntimeException('Transaksi masih pending atau belum ditemukan di Ethereum Mainnet.');
        }
        if (strtolower((string) ($receipt['status'] ?? '')) !== '0x1' || empty($receipt['blockNumber'])) {
            throw new RuntimeException('Transaksi Ethereum gagal atau belum masuk ke blok.');
        }
        if (strtolower((string) ($transaction['from'] ?? '')) !== strtolower($wallet)) {
            throw new RuntimeException('Alamat pengirim tidak cocok dengan wallet yang terhubung.');
        }
        if (strtolower((string) ($transaction['to'] ?? '')) !== strtolower(config('pulsanium.eth.merchant'))) {
            throw new RuntimeException('Alamat penerima transaksi tidak cocok.');
        }

        $paidWei = $this->hexToDecimal((string) ($transaction['value'] ?? '0x0'));
        if (bccomp($paidWei, $this->toWei($expectedEth), 0) < 0) {
            throw new RuntimeException('Nominal ETH yang diterima kurang dari total barter.');
        }

        return [
            'transaction_hash' => strtolower($txHash),
            'block_number' => (string) $receipt['blockNumber'],
            'from' => strtolower($wallet),
            'to' => strtolower((string) $transaction['to']),
            'value_wei' => $paidWei,
            'status' => 'confirmed',
        ];
    }

    private function rpc(string $method, array $params): mixed
    {
        $body = Http::timeout(15)->post(config('pulsanium.eth.rpc_url'), [
            'jsonrpc' => '2.0', 'method' => $method, 'params' => $params, 'id' => 1,
        ])->throw()->json();
        if (isset($body['error'])) {
            throw new RuntimeException('Ethereum RPC error: '.($body['error']['message'] ?? 'unknown error'));
        }

        return $body['result'] ?? null;
    }

    private function hexToDecimal(string $hex): string
    {
        $hex = preg_replace('/^0x/', '', strtolower(trim($hex))) ?: '0';
        $decimal = '0';
        foreach (str_split($hex) as $digit) {
            $decimal = bcadd(bcmul($decimal, '16', 0), (string) hexdec($digit), 0);
        }

        return $decimal;
    }

    private function toWei(string $amount): string
    {
        if (! preg_match('/^(\d+)(?:\.(\d+))?$/', $amount, $match)) {
            throw new RuntimeException('Nominal ETH tidak valid.');
        }

        return bcadd(bcmul($match[1], bcpow('10', '18', 0), 0), str_pad(substr($match[2] ?? '', 0, 18), 18, '0'), 0);
    }
}
