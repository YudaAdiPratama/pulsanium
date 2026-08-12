<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PurchasePageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Schema::create('harga', function (Blueprint $table) {
            $table->string('operator');
            $table->string('kode');
            $table->string('deskripsi');
            $table->unsignedInteger('harga_jual');
        });
        DB::table('harga')->insert(['operator' => 'TELKOMSEL', 'kode' => 'S5', 'deskripsi' => 'Pulsa 5.000', 'harga_jual' => 6000]);
        Http::fake([config('pulsanium.rates_url') => Http::response(['vexanium' => ['idr' => 1000], 'ethereum' => ['idr' => 50000000]])]);
    }

    public function test_page_combines_both_payment_methods_and_products(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Vexanium')
            ->assertSee('Ethereum')
            ->assertSee('Pulsa 5.000');
    }

    public function test_order_requires_a_valid_wallet_transaction_and_phone(): void
    {
        $this->post('/pesanan', [
            'payment_method' => 'vex', 'wallet_account' => '!', 'phone' => '123',
            'package' => 'TELKOMSEL|S5', 'transaction_id' => 'x',
        ])->assertSessionHasErrors(['wallet_account', 'phone', 'transaction_id']);
    }
}
