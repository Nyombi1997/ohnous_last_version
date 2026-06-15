<?php

$payload = file_get_contents('php://input');

file_put_contents(
    __DIR__ . '/webhook.log',
    date('Y-m-d H:i:s') . PHP_EOL .
    $payload . PHP_EOL . PHP_EOL,
    FILE_APPEND
);

http_response_code(200);
echo 'OK';