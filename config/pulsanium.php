<?php

return [
    'rates_url' => 'https://api.coingecko.com/api/v3/simple/price?ids=vexanium,ethereum&vs_currencies=idr',
    'vex' => [
        'merchant' => env('VEX_MERCHANT_ACCOUNT', 'pertapavex23'),
        'fallback_rate_idr' => (float) env('VEX_FALLBACK_RATE_IDR', 1000),
        'markup_percent' => (float) env('VEX_MARKUP_PERCENT', 20),
    ],
    'eth' => [
        'merchant' => env('ETH_MERCHANT_ADDRESS', '0x6807998a3668650999956600d9e310b092815a09'),
        'chain_id' => env('ETHEREUM_CHAIN_ID', '0x1'),
        'rpc_url' => env('ETHEREUM_RPC_URL', 'https://ethereum-rpc.publicnode.com'),
        'fallback_rate_idr' => (float) env('ETH_FALLBACK_RATE_IDR', 50000000),
        'markup_percent' => (float) env('ETH_MARKUP_PERCENT', 20),
    ],
    'gowa' => [
        'url' => env('GOWA_URL', 'http://192.168.18.90:3000/send/message'),
        'device_id' => env('GOWA_DEVICE_ID'),
        'target_phone' => env('GOWA_TARGET_PHONE', '6281556671111@s.whatsapp.net'),
        'pin' => env('PULSANIUM_PIN', '4789'),
    ],
];
