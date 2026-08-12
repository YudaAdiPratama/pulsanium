<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('barter_orders', function (Blueprint $table) {
            $table->string('id', 50)->primary();
            $table->enum('payment_method', ['vex', 'eth']);
            $table->string('wallet_account', 64);
            $table->string('chain_id', 20)->nullable();
            $table->string('phone', 20)->index();
            $table->string('operator', 50);
            $table->string('product_code', 100);
            $table->string('product_description');
            $table->unsignedInteger('price_idr');
            $table->decimal('crypto_rate_idr', 24, 8);
            $table->string('rate_source', 30);
            $table->decimal('base_crypto_amount', 36, 18);
            $table->decimal('markup_percent', 6, 2)->default(20);
            $table->decimal('crypto_amount', 36, 18);
            $table->string('merchant_account', 64);
            $table->string('transaction_id', 128)->unique();
            $table->string('transaction_block', 32)->nullable();
            $table->json('transaction_result')->nullable();
            $table->string('note', 200)->nullable();
            $table->enum('status', ['PAID', 'SENT', 'FAILED'])->default('PAID')->index();
            $table->string('gowa_message')->nullable();
            $table->json('gowa_response')->nullable();
            $table->text('last_error')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('barter_orders');
    }
};
