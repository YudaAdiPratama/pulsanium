<?php

namespace App\Http\Controllers;

use App\Models\BarterOrder;
use App\Services\CryptoRateService;
use App\Services\EthereumPaymentVerifier;
use App\Services\GowaService;
use App\Services\OperatorDetector;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PurchaseController extends Controller
{
    public function index(Request $request, CryptoRateService $rateService)
    {
        $products = DB::table('harga')
            ->select(['operator', 'kode', 'deskripsi', 'harga_jual'])
            ->orderBy('operator')->orderBy('harga_jual')->orderBy('deskripsi')->get()
            ->map(fn ($row) => [
                'key' => strtoupper($row->operator).'|'.$row->kode,
                'operator' => strtoupper($row->operator),
                'code' => (string) $row->kode,
                'description' => (string) $row->deskripsi,
                'price' => (int) $row->harga_jual,
            ]);

        $rates = $rateService->rates();
        $quotes = [];
        foreach ($products as $product) {
            $quotes[$product['key']] = [
                'price' => $product['price'],
                'vex' => $this->quote($product['price'], $rates['vex'], (float) config('pulsanium.vex.markup_percent'), 4),
                'eth' => $this->quote($product['price'], $rates['eth'], (float) config('pulsanium.eth.markup_percent'), 8),
            ];
        }
        $request->session()->put('barter_quotes', $quotes);

        return view('purchase', compact('products', 'rates', 'quotes'));
    }

    public function store(
        Request $request,
        OperatorDetector $detector,
        EthereumPaymentVerifier $ethereumVerifier,
        GowaService $gowa
    ) {
        $data = $request->validate([
            'payment_method' => ['required', 'in:vex,eth'],
            'wallet_account' => ['required', 'string', 'max:64'],
            'chain_id' => ['nullable', 'string', 'max:20'],
            'phone' => ['required', 'string'],
            'package' => ['required', 'string', 'max:160'],
            'transaction_id' => ['required', 'string', 'max:128'],
            'transaction_result' => ['nullable', 'json'],
            'note' => ['nullable', 'string', 'max:200'],
        ]);

        $method = $data['payment_method'];
        $phone = preg_replace('/\D+/', '', $data['phone']);
        $operator = $detector->detect($phone);
        $wallet = strtolower(trim($data['wallet_account']));
        $txId = strtolower(trim($data['transaction_id']));

        $errors = [];
        if (strlen($phone) < 10 || strlen($phone) > 15) {
            $errors['phone'] = 'Nomor HP harus terdiri dari 10 sampai 15 digit.';
        }
        if (! $operator) {
            $errors['phone'] = 'Operator nomor HP tidak dikenali.';
        }
        if ($method === 'vex' && ! preg_match('/^[a-z1-5.]{3,13}$/', $wallet)) {
            $errors['wallet_account'] = 'Akun VEX tidak valid.';
        }
        if ($method === 'eth' && ! preg_match('/^0x[a-f0-9]{40}$/', $wallet)) {
            $errors['wallet_account'] = 'Alamat Ethereum tidak valid.';
        }
        if ($method === 'eth' && strtolower((string) ($data['chain_id'] ?? '')) !== strtolower(config('pulsanium.eth.chain_id'))) {
            $errors['chain_id'] = 'Barter harus menggunakan Ethereum Mainnet.';
        }
        if ($method === 'eth' && ! preg_match('/^0x[a-f0-9]{64}$/', $txId)) {
            $errors['transaction_id'] = 'Hash transaksi Ethereum tidak valid.';
        }
        if ($method === 'vex' && strlen($txId) < 8) {
            $errors['transaction_id'] = 'TX ID VEX tidak valid.';
        }
        if ($errors) {
            throw ValidationException::withMessages($errors);
        }

        $productRow = DB::table('harga')->whereRaw("CONCAT(UPPER(operator), '|', kode) = ?", [$data['package']])->first();
        if (! $productRow) {
            throw ValidationException::withMessages(['package' => 'Produk pulsa tidak tersedia.']);
        }
        if ($operator && strtoupper($productRow->operator) !== $operator['database']) {
            throw ValidationException::withMessages(['package' => 'Produk pulsa tidak sesuai dengan operator nomor HP.']);
        }

        $quote = $request->session()->get("barter_quotes.{$data['package']}.{$method}");
        if (! is_array($quote) || (int) ($quote['price'] ?? 0) !== (int) $productRow->harga_jual) {
            throw ValidationException::withMessages(['package' => 'Kutipan harga sudah kedaluwarsa. Muat ulang halaman.']);
        }

        if (BarterOrder::where('transaction_id', $txId)->exists()) {
            throw ValidationException::withMessages(['transaction_id' => 'Transaksi ini sudah digunakan untuk pesanan lain.']);
        }

        $verification = $data['transaction_result'] ? json_decode($data['transaction_result'], true) : [];
        if ($method === 'eth') {
            try {
                $verification = $ethereumVerifier->verify($txId, $wallet, (string) $quote['amount']);
            } catch (\Throwable $e) {
                throw ValidationException::withMessages(['transaction_id' => 'Barter belum dapat diverifikasi: '.$e->getMessage()]);
            }
        }

        try {
            $order = DB::transaction(fn () => BarterOrder::create([
                'id' => strtoupper($method).'-PLS-'.now()->format('Ymd-His').'-'.random_int(100, 999),
                'payment_method' => $method,
                'wallet_account' => $wallet,
                'chain_id' => $method === 'eth' ? config('pulsanium.eth.chain_id') : null,
                'phone' => $phone,
                'operator' => $operator['name'],
                'product_code' => (string) $productRow->kode,
                'product_description' => (string) $productRow->deskripsi,
                'price_idr' => (int) $productRow->harga_jual,
                'crypto_rate_idr' => $quote['rate'],
                'rate_source' => $quote['source'],
                'base_crypto_amount' => $quote['base_amount'],
                'markup_percent' => $quote['markup_percent'],
                'crypto_amount' => $quote['amount'],
                'merchant_account' => config("pulsanium.{$method}.merchant"),
                'transaction_id' => $txId,
                'transaction_block' => $verification['block_number'] ?? null,
                'transaction_result' => $verification,
                'note' => $data['note'] ?? null,
                'status' => 'PAID',
            ]));
        } catch (QueryException $e) {
            if ($e->getCode() === '23000') {
                throw ValidationException::withMessages(['transaction_id' => 'Transaksi ini sudah digunakan untuk pesanan lain.']);
            }
            throw $e;
        }

        $gowa->submit($order);
        $request->session()->forget("barter_quotes.{$data['package']}");

        return redirect()->route('purchase.index')->with('success_order', $order->fresh()->toArray());
    }

    private function quote(int $price, array $rate, float $markup, int $precision): array
    {
        $base = $rate['value'] > 0 ? $price / $rate['value'] : 0;

        return [
            'price' => $price,
            'rate' => $rate['value'],
            'source' => $rate['source'],
            'base_amount' => number_format($base, $precision, '.', ''),
            'markup_percent' => $markup,
            'amount' => number_format($base * (1 + $markup / 100), $precision, '.', ''),
        ];
    }
}
