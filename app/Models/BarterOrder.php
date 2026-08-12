<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BarterOrder extends Model
{
    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'transaction_result' => 'array',
            'gowa_response' => 'array',
            'sent_at' => 'datetime',
            'crypto_rate_idr' => 'decimal:8',
            'base_crypto_amount' => 'decimal:18',
            'crypto_amount' => 'decimal:18',
        ];
    }
}
