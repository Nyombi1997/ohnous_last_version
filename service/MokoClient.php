<?php

class MokoClient
{
    private $config;
    private $transport;

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
        if (!$this->config['public_key'] || !$this->config['secret_key']) throw new RuntimeException('Configurez les clés API Moko sur le serveur.');
        if (!preg_match('~^https://[^/?#]+$~D', $this->config['base_url'])) throw new RuntimeException('MOKO_BASE_URL doit être une origine HTTPS.');
        ksort($query, SORT_STRING);
        $path .= $query ? '?'.http_build_query($query, '', '&', PHP_QUERY_RFC3986) : '';
        $body = $payload === null ? '' : json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        $ts = sprintf('%.0f', microtime(true) * 1000);
        $nonce = self::nonce();
        $headers = ['Content-Type: application/json', 'Accept: application/json', 'X-Moko-Key: '.$this->config['public_key'], 'X-Moko-Timestamp: '.$ts, 'X-Moko-Nonce: '.$nonce,
            'X-Moko-Signature: '.self::signature($this->config['secret_key'], $method, $path, $body, $ts, $nonce)];
        if ($this->transport) return ($this->transport)($method, $path, $body, $headers);
        if (!function_exists('curl_init')) throw new RuntimeException('Extension PHP cURL requise.');
        $curl = curl_init($this->config['base_url'].$path);
        curl_setopt_array($curl, [CURLOPT_CUSTOMREQUEST => $method, CURLOPT_HTTPHEADER => $headers, CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 5, CURLOPT_TIMEOUT => 20, CURLOPT_SSL_VERIFYPEER => true, CURLOPT_SSL_VERIFYHOST => 2, CURLOPT_FOLLOWLOCATION => false]);
        if ($payload !== null) curl_setopt($curl, CURLOPT_POSTFIELDS, $body);
        $raw = curl_exec($curl);
        $status = (int)curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        $data = $raw === false ? null : json_decode($raw, true);
        return ['http' => $status, 'data' => is_array($data) ? $data : [], 'valid' => is_array($data)];
    }
}
