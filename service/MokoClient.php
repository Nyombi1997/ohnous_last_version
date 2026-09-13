<?php

class MokoClient
{
    private $config;
    private $transport;
    private $lastExchange = [];

    public function __construct(array $config, ?callable $transport = null)
    {
        $this->config = $config;
        $this->transport = $transport;
    }

    public static function nonce()
    {
        $bytes = random_bytes(16);
        $bytes[6] = chr((ord($bytes[6]) & 15) | 64);
        $bytes[8] = chr((ord($bytes[8]) & 63) | 128);
        $hex = bin2hex($bytes);
        return substr($hex, 0, 8).'-'.substr($hex, 8, 4).'-'.substr($hex, 12, 4).'-'.substr($hex, 16, 4).'-'.substr($hex, 20);
    }

    public static function signature($secret, $method, $path, $body, $timestamp, $nonce)
    {
        return hash_hmac('sha256', implode("\n", [strtoupper($method), $path, hash('sha256', $body), $timestamp, $nonce]), $secret);
    }

    public static function verifyWebhook($raw, $header, $secret)
    {
        if ($secret === '' || !preg_match('/^t=(\d{13}),v1=([a-f0-9]{64})$/D', $header, $m)) return false;
        // The guide does not specify a webhook freshness window; delayed deliveries are valid.
        return hash_equals(hash_hmac('sha256', $m[1].'.'.$raw, $secret), $m[2]);
    }

    public function request($method, $path, ?array $payload = null, array $query = [])
    {
        $this->lastExchange = [];
        // Les tentatives Create Payout sont persistées et planifiées par le service.
        $attemptLimit = strtoupper($method) === 'POST' && $path === '/v1/payouts' ? 1 : 3;
        for ($attempt = 1; $attempt <= $attemptLimit; $attempt++) {
            $response = $this->requestOnce($method, $path, $payload, $query);
            if ($attempt === $attemptLimit || !($response['http'] === 0 || $response['http'] === 429 || $response['http'] >= 500)) return $response;
            usleep(250000 * (2 ** ($attempt - 1)));
        }
    }

    private function requestOnce($method, $path, ?array $payload, array $query)
    {
        $method = strtoupper($method);
        if (!$this->config['public_key'] || !$this->config['secret_key']) throw new RuntimeException('Configurez les clés API Moko sur le serveur.');
        if (!preg_match('~^https://[^/?#]+$~D', $this->config['base_url'])) throw new RuntimeException('MOKO_BASE_URL doit être une origine HTTPS.');
        ksort($query, SORT_STRING);
        $path .= $query ? '?'.http_build_query($query, '', '&', PHP_QUERY_RFC3986) : '';
        $body = $payload === null ? '' : json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        $ts = (string)(int)floor(microtime(true) * 1000);
        $nonce = self::nonce();
        $headers = ['Content-Type: application/json', 'Accept: application/json', 'X-Moko-Key: '.$this->config['public_key'], 'X-Moko-Timestamp: '.$ts, 'X-Moko-Nonce: '.$nonce,
            'X-Moko-Signature: '.self::signature($this->config['secret_key'], $method, $path, $body, $ts, $nonce)];
        if ($this->transport) {
            $response = ($this->transport)($method, $path, $body, $headers);
            return $this->record($method, $path, $body, $ts, $nonce, $response);
        }
        if (!function_exists('curl_init')) throw new RuntimeException('Extension PHP cURL requise.');
        $curl = curl_init($this->config['base_url'].$path);
        $serverDate = null;
        curl_setopt_array($curl, [CURLOPT_CUSTOMREQUEST => $method, CURLOPT_HTTPHEADER => $headers, CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 5, CURLOPT_TIMEOUT => 20, CURLOPT_SSL_VERIFYPEER => true, CURLOPT_SSL_VERIFYHOST => 2, CURLOPT_FOLLOWLOCATION => false]);
        curl_setopt($curl, CURLOPT_HEADERFUNCTION, static function ($curl, $line) use (&$serverDate) {
            if (stripos($line, 'Date:') === 0) $serverDate = trim(substr($line, 5));
            return strlen($line);
        });
        if ($payload !== null) curl_setopt($curl, CURLOPT_POSTFIELDS, $body);
        $raw = curl_exec($curl);
        $status = (int)curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $errno = curl_errno($curl);
        $error = curl_error($curl);
        curl_close($curl);
        $data = $raw === false ? null : json_decode($raw, true);
        return $this->record($method, $path, $body, $ts, $nonce, ['http' => $status, 'data' => is_array($data) ? $data : [], 'valid' => is_array($data), 'raw'=>$raw === false ? '' : $raw, 'transport_code'=>$errno, 'transport_error'=>$error, 'server_date'=>$serverDate]);
    }

    public function lastExchange()
    {
        return $this->lastExchange;
    }

    public function redact($value)
    {
        if (is_array($value)) {
            foreach ($value as $key => $item) {
                $value[$key] = preg_match('/secret|signature|authorization|password|token|public_key|x-moko-key/i', (string)$key) ? '[masqué]' : $this->redact($item);
            }
            return $value;
        }
        if (!is_string($value)) return $value;
        foreach (['public_key','secret_key','webhook_secret'] as $key) {
            $secret = $this->config[$key] ?? '';
            if ($secret !== '') {
                $value = str_replace([$secret, substr(json_encode($secret), 1, -1), rawurlencode($secret)], '[masqué]', $value);
            }
        }
        $value = preg_replace('/\b(?:sk|pk)_(?:live|test)_[A-Za-z0-9_-]+/', '[masqué]', $value);
        return preg_replace('/("(?:[^"\\\\]*)(?:secret|signature|password|token|public_key|authorization|x-moko-key)(?:[^"\\\\]*)"\s*:\s*)"(?:\\\\.|[^"\\\\])*"/i', '$1"[masqué]"', $value);
    }

    private function record($method, $path, $body, $timestamp, $nonce, array $response)
    {
        $data = $response['data'];
        $request = json_decode($body, true) ?: [];
        $detail = is_array($data['detail'] ?? null) ? $data['detail'] : [];
        $signature = self::signature($this->config['secret_key'], $method, $path, $body, $timestamp, $nonce);
        $rawResponse = $response['raw'] ?? json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $this->lastExchange = $this->redact([
            'date'=>gmdate('c'), 'endpoint'=>$method.' '.$path, 'http'=>$response['http'],
            'merchant_reference'=>$request['merchant_reference'] ?? $data['merchant_reference'] ?? null,
            'merchant_recipient_id'=>$request['merchant_recipient_id'] ?? $data['merchant_recipient_id'] ?? null,
            'recipient_id'=>$data['recipient_id'] ?? $request['recipient_id'] ?? null,
            'payout_id'=>$data['payout_id'] ?? null, 'status'=>$data['status'] ?? null,
            'error_code'=>$detail['code'] ?? null, 'error_message'=>$detail['message'] ?? null,
            'transport_code'=>$response['transport_code'] ?? 0, 'transport_error'=>$response['transport_error'] ?? '',
            'server_date'=>$response['server_date'] ?? null,
            'headers'=>['Content-Type'=>'application/json','Accept'=>'application/json','X-Moko-Key'=>'[masqué]', 'X-Moko-Timestamp'=>$timestamp,'X-Moko-Nonce'=>$nonce,'X-Moko-Signature'=>'[masqué]'],
            'body_sha256'=>hash('sha256', $body), 'request_body'=>$body,
            'response_raw'=>str_replace($signature, '[masqué]', $rawResponse),
        ]);
        if (!empty($this->config['debug'])) {
            $file = __DIR__.'/../logs/moko-payout-debug.log';
            // Rotation bornée ; logs/.htaccess interdit leur téléchargement HTTP.
            $handle = @fopen($file, 'c+');
            if ($handle) {
                if (flock($handle, LOCK_EX)) {
                    $size = fstat($handle)['size'];
                    if ($size > 5 * 1024 * 1024) ftruncate($handle, 0);
                    fseek($handle, 0, SEEK_END);
                    fwrite($handle, json_encode($this->lastExchange, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE).PHP_EOL);
                    flock($handle, LOCK_UN);
                }
                fclose($handle);
            }
        }
        return $response;
    }
}
