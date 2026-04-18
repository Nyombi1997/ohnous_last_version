<?php

return [
    'currency' => 'USD',
    'freshpay' => [
        'mode' => (function () {
            $mode = strtolower(trim((string)(getenv('FRESHPAY_MODE') ?: 'production')));
            return in_array($mode, ['prod', 'production'], true) ? 'production' : 'test';
        })(),
        'secret_key' => getenv('FRESHPAY_SECRET_KEY4') ?: '',
        'hmac_key' => getenv('FRESHPAY_HMAC_KEY4') ?: '',
        'merchant_id' => getenv('FRESHPAY_MERCHANT_ID') ?: '',
        'merchant_secret' => getenv('FRESHPAY_MERCHANT_SECRET') ?: '',
        'callback_url' => getenv('FRESHPAY_CALLBACK_URL') ?: '',
        'method_map' => [
            'airtel' => getenv('FRESHPAY_METHOD_AIRTEL') ?: 'airtel',
            'orange' => getenv('FRESHPAY_METHOD_ORANGE') ?: 'orange',
            'mpesa' => getenv('FRESHPAY_METHOD_MPESA') ?: 'mpesa',
            // TODO FreshPay : la doc publique MOKO Afrika mentionne aussi "africell".
            'afrimoney' => getenv('FRESHPAY_METHOD_AFRIMONEY') ?: 'afrimoney',
            'visa' => getenv('FRESHPAY_METHOD_VISA') ?: 'visa',
        ],
        'endpoints' => [
            'test' => [
                'initiate' => getenv('FRESHPAY_TEST_INITIATE_URL') ?: '',
                'status' => getenv('FRESHPAY_TEST_STATUS_URL') ?: '',
            ],
            'production' => [
                'initiate' => getenv('FRESHPAY_PROD_INITIATE_URL') ?: 'https://paydrc.gofreshbakery.net/api/v5/',
                'status' => getenv('FRESHPAY_PROD_STATUS_URL') ?: 'https://paydrc.gofreshbakery.net/api/v5/',
            ],
        ],
        'http' => [
            'timeout' => (int)(getenv('FRESHPAY_HTTP_TIMEOUT') ?: 20),
            'connect_timeout' => (int)(getenv('FRESHPAY_HTTP_CONNECT_TIMEOUT') ?: 10),
            'request_format' => strtolower(trim((string)(getenv('FRESHPAY_REQUEST_FORMAT') ?: 'json'))),
        ],
        'callback' => [
            'signature_headers' => [
                'HTTP_X_SIGNATURE',
                'HTTP_X_FRESHPAY_SIGNATURE',
                'HTTP_SIGNATURE',
            ],
            'signature_field' => getenv('FRESHPAY_CALLBACK_SIGNATURE_FIELD') ?: 'signature',
            'encrypted_field' => getenv('FRESHPAY_CALLBACK_ENCRYPTED_FIELD') ?: 'data',
            'status_field' => getenv('FRESHPAY_CALLBACK_STATUS_FIELD') ?: 'Status',
            'trans_status_field' => getenv('FRESHPAY_CALLBACK_TRANS_STATUS_FIELD') ?: 'Trans_Status',
            'description_field' => getenv('FRESHPAY_CALLBACK_DESCRIPTION_FIELD') ?: 'Trans_Status_Description',
            'transaction_id_field' => getenv('FRESHPAY_CALLBACK_TRANSACTION_ID_FIELD') ?: 'Transaction_id',
            'financial_institution_id_field' => getenv('FRESHPAY_CALLBACK_FINANCIAL_INSTITUTION_ID_FIELD') ?: 'Financial_Institution_id',
            'decrypt_mode' => strtolower(trim((string)(getenv('FRESHPAY_CALLBACK_DECRYPT_MODE') ?: 'aes'))),
            'decrypt_cipher' => getenv('FRESHPAY_CALLBACK_DECRYPT_CIPHER') ?: 'AES-128-CBC',
            'decrypt_iv_field' => getenv('FRESHPAY_CALLBACK_DECRYPT_IV_FIELD') ?: 'iv',
        ],
        'visa' => [
            'enabled' => (int)(getenv('FRESHPAY_ENABLE_VISA') ?: 0) === 1,
            'shared_initiate_endpoint' => (int)(getenv('FRESHPAY_VISA_SHARED_ENDPOINT') ?: 1) === 1,
        ],
    ],
];
