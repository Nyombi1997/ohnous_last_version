<?php

return [
    'enabled' => true,
    'currency' => 'USD',
    'display_currency' => 'USD',
    'freshpay' => [
        'mode' => 'production',

        'secret_key' => '4357975872d4498e',
        'hmac_key' => '2f76bc4319f04357',
        'merchant_id' => 'jV]M|@2gr{b+G])6b',
        'merchant_secret' => 'jz5epFB9Z2xfr!nNJb',
        'callback_url' => '',
        'customer_profile' => [
            // Profil figé validé par FreshPay : ne pas le remplacer par les champs saisis au checkout.
            'firstname' => 'Edo',
            'lastname' => 'systeme',
            'email' => 'edosysteme@gmail.com',
        ],

        'method_map' => [
            'airtel' => 'airtel',
            'orange' => 'orange',
            'mpesa' => 'mpesa',
            'afrimoney' => 'afrimoney',

            // 'visa' => 'visa',
        ],

        'endpoints' => [
            'production' => [
                'initiate' => 'https://paydrc.gofreshbakery.net/api/v5/',
                'status' => 'https://paydrc.gofreshbakery.net/api/v5/',
            ],
        ],

        'http' => [
            'timeout' => 20,
            'connect_timeout' => 10,
            'request_format' => 'json',
        ],

        'callback' => [
            'signature_headers' => [
                'HTTP_X_SIGNATURE',
                'HTTP_X_FRESHPAY_SIGNATURE',
                'HTTP_SIGNATURE',
            ],
            'signature_field' => 'signature',
            'encrypted_field' => 'data',
            'status_field' => 'Status',
            'trans_status_field' => 'Trans_Status',
            'description_field' => 'Trans_Status_Description',
            'transaction_id_field' => 'Transaction_id',
            'financial_institution_id_field' => 'Financial_Institution_id',

            'decrypt_mode' => 'aes',
            'decrypt_cipher' => 'AES-128-CBC',
            'decrypt_iv_field' => 'iv',
        ],

        'visa' => [
            'enabled' => false,
            'shared_initiate_endpoint' => true,
        ],
    ],
];
