<?php

class WhatsAppService
{
    private $accessToken;
    private $phoneNumberId;
    private $apiVersion;
    private $pdo;

    public function __construct(PDO $pdo = null)
    {
        require_once dirname(__DIR__) . '/config/whatsapp.php';

        $this->accessToken = WHATSAPP_ACCESS_TOKEN;
        $this->phoneNumberId = WHATSAPP_PHONE_NUMBER_ID;
        $this->apiVersion = WHATSAPP_API_VERSION;
        $this->pdo = $pdo;
    }

    public static function ensureMessagesTable(PDO $pdo)
    {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS whatsapp_messages (
                id INT AUTO_INCREMENT PRIMARY KEY,
                wa_message_id VARCHAR(255) UNIQUE NULL,
                wa_id VARCHAR(50) NULL,
                phone VARCHAR(50) NULL,
                contact_name VARCHAR(255) NULL,
                message_type VARCHAR(50) NULL,
                message_body TEXT NULL,
                raw_payload LONGTEXT NULL,
                direction ENUM('in','out') DEFAULT 'in',
                status VARCHAR(50) NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
    }

    public static function log($message, array $context = [])
    {
        $logDir = dirname(__DIR__) . '/logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0775, true);
        }

        $line = '[' . date('Y-m-d H:i:s') . '] ' . $message;
        if (!empty($context)) {
            $line .= ' ' . json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        file_put_contents($logDir . '/whatsapp.log', $line . PHP_EOL, FILE_APPEND | LOCK_EX);
    }

    public function sendTemplate($to, $templateName, $languageCode = 'en_US', $components = [])
    {
        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => $this->normalizePhone($to),
            'type' => 'template',
            'template' => [
                'name' => $templateName,
                'language' => [
                    'code' => $languageCode
                ]
            ]
        ];

        if (!empty($components)) {
            $payload['template']['components'] = $components;
        }

        return $this->sendMessage($payload, $to, 'template', $templateName);
    }

    public function sendText($to, $message)
    {
        $payload = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $this->normalizePhone($to),
            'type' => 'text',
            'text' => [
                'preview_url' => false,
                'body' => $message
            ]
        ];

        return $this->sendMessage($payload, $to, 'text', $message);
    }

    public function markMessageAsRead($messageId)
    {
        $payload = [
            'messaging_product' => 'whatsapp',
            'status' => 'read',
            'message_id' => $messageId
        ];

        return $this->request('POST', $this->messagesUrl(), $payload);
    }

    private function sendMessage(array $payload, $phone, $type, $body)
    {
        $response = $this->request('POST', $this->messagesUrl(), $payload);
        $status = !empty($response['success']) ? 'sent' : 'error';
        $messageId = $response['data']['messages'][0]['id'] ?? null;

        if ($this->pdo instanceof PDO) {
            self::ensureMessagesTable($this->pdo);
            $stmt = $this->pdo->prepare("
                INSERT INTO whatsapp_messages
                    (wa_message_id, wa_id, phone, contact_name, message_type, message_body, raw_payload, direction, status)
                VALUES
                    (:wa_message_id, NULL, :phone, NULL, :message_type, :message_body, :raw_payload, 'out', :status)
                ON DUPLICATE KEY UPDATE status = VALUES(status), raw_payload = VALUES(raw_payload)
            ");
            $stmt->execute([
                ':wa_message_id' => $messageId,
                ':phone' => $phone,
                ':message_type' => $type,
                ':message_body' => $body,
                ':raw_payload' => json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                ':status' => $status
            ]);
        }

        return $response;
    }

    private function request($method, $url, array $payload)
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->accessToken,
                'Content-Type: application/json'
            ],
            CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            CURLOPT_TIMEOUT => 15
        ]);

        $body = curl_exec($ch);
        $errno = curl_errno($ch);
        $error = curl_error($ch);
        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($errno) {
            self::log('WhatsApp cURL error', ['errno' => $errno, 'error' => $error]);
            return [
                'success' => false,
                'http_code' => $httpCode,
                'error' => $error
            ];
        }

        $decoded = json_decode((string)$body, true);
        $success = $httpCode >= 200 && $httpCode < 300;

        if (!$success) {
            self::log('WhatsApp API error', ['http_code' => $httpCode, 'body' => $decoded ?: $body]);
        }

        return [
            'success' => $success,
            'http_code' => $httpCode,
            'data' => is_array($decoded) ? $decoded : null,
            'raw' => $body
        ];
    }

    private function messagesUrl()
    {
        return 'https://graph.facebook.com/' . $this->apiVersion . '/' . $this->phoneNumberId . '/messages';
    }

    private function normalizePhone($phone)
    {
        return preg_replace('/[^\d]/', '', (string)$phone);
    }
}
