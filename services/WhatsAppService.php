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
        self::ensureTables($pdo);
    }

    public static function ensureTables(PDO $pdo)
    {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS whatsapp_conversations (
                id INT AUTO_INCREMENT PRIMARY KEY,
                wa_id VARCHAR(50) UNIQUE NOT NULL,
                phone VARCHAR(50) NULL,
                customer_id INT NULL,
                contact_name VARCHAR(255) NULL,
                last_message_at DATETIME NULL,
                unread_count INT DEFAULT 0,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_whatsapp_conversations_wa_id (wa_id),
                INDEX idx_whatsapp_conversations_customer_id (customer_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS whatsapp_messages (
                id INT AUTO_INCREMENT PRIMARY KEY,
                conversation_id INT NOT NULL,
                wa_message_id VARCHAR(255) UNIQUE NULL,
                direction ENUM('in','out') NOT NULL DEFAULT 'in',
                message_type VARCHAR(50) NULL,
                message_body TEXT NULL,
                status VARCHAR(50) NULL,
                raw_payload LONGTEXT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_whatsapp_messages_conversation_id (conversation_id),
                INDEX idx_whatsapp_messages_wa_message_id (wa_message_id),
                INDEX idx_whatsapp_messages_created_at (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        self::migrateLegacyMessages($pdo);
        self::ensureIndex($pdo, 'whatsapp_conversations', 'idx_whatsapp_conversations_wa_id', 'wa_id');
        self::ensureIndex($pdo, 'whatsapp_conversations', 'idx_whatsapp_conversations_customer_id', 'customer_id');
        self::ensureIndex($pdo, 'whatsapp_messages', 'idx_whatsapp_messages_conversation_id', 'conversation_id');
        self::ensureIndex($pdo, 'whatsapp_messages', 'idx_whatsapp_messages_wa_message_id', 'wa_message_id');
        self::ensureIndex($pdo, 'whatsapp_messages', 'idx_whatsapp_messages_created_at', 'created_at');
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

    public function markAsRead($messageId)
    {
        $payload = [
            'messaging_product' => 'whatsapp',
            'status' => 'read',
            'message_id' => $messageId
        ];

        return $this->request('POST', $this->messagesUrl(), $payload);
    }

    public function markMessageAsRead($messageId)
    {
        return $this->markAsRead($messageId);
    }

    private function sendMessage(array $payload, $phone, $type, $body)
    {
        $response = $this->request('POST', $this->messagesUrl(), $payload);
        $status = !empty($response['success']) ? 'sent' : 'error';
        $messageId = $response['data']['messages'][0]['id'] ?? null;

        if ($this->pdo instanceof PDO) {
            self::ensureTables($this->pdo);
            $conversationId = self::upsertConversation($this->pdo, $this->normalizePhone($phone), $phone, null, false);
            $stmt = $this->pdo->prepare("
                INSERT INTO whatsapp_messages
                    (conversation_id, wa_message_id, message_type, message_body, raw_payload, direction, status)
                VALUES
                    (:conversation_id, :wa_message_id, :message_type, :message_body, :raw_payload, 'out', :status)
                ON DUPLICATE KEY UPDATE status = VALUES(status), raw_payload = VALUES(raw_payload)
            ");
            $stmt->execute([
                ':conversation_id' => $conversationId,
                ':wa_message_id' => $messageId,
                ':message_type' => $type,
                ':message_body' => $body,
                ':raw_payload' => json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                ':status' => $status
            ]);
            self::touchConversation($this->pdo, $conversationId, date('Y-m-d H:i:s'));
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

    public static function normalizePhoneNumber($phone)
    {
        return preg_replace('/[^\d]/', '', (string)$phone);
    }

    public static function upsertConversation(PDO $pdo, $waId, $phone = null, $contactName = null, $incrementUnread = false, $lastMessageAt = null)
    {
        $waId = self::normalizePhoneNumber($waId);
        if ($waId === '') {
            $waId = self::normalizePhoneNumber($phone);
        }

        if ($waId === '') {
            throw new InvalidArgumentException('wa_id WhatsApp manquant.');
        }

        $phone = $phone !== null && trim((string)$phone) !== '' ? (string)$phone : $waId;
        $lastMessageAt = $lastMessageAt ?: date('Y-m-d H:i:s');
        $customerId = self::findCustomerIdByPhone($pdo, $waId, $phone);

        $stmt = $pdo->prepare("SELECT id FROM whatsapp_conversations WHERE wa_id = :wa_id LIMIT 1");
        $stmt->execute([':wa_id' => $waId]);
        $conversationId = (int)$stmt->fetchColumn();

        if ($conversationId > 0) {
            $sql = "
                UPDATE whatsapp_conversations
                SET phone = :phone,
                    contact_name = COALESCE(NULLIF(:contact_name, ''), contact_name),
                    customer_id = COALESCE(:customer_id, customer_id),
                    last_message_at = :last_message_at,
                    unread_count = unread_count + :unread_increment
                WHERE id = :id
            ";
            $update = $pdo->prepare($sql);
            $update->execute([
                ':phone' => $phone,
                ':contact_name' => $contactName,
                ':customer_id' => $customerId ?: null,
                ':last_message_at' => $lastMessageAt,
                ':unread_increment' => $incrementUnread ? 1 : 0,
                ':id' => $conversationId
            ]);

            return $conversationId;
        }

        $insert = $pdo->prepare("
            INSERT INTO whatsapp_conversations
                (wa_id, phone, customer_id, contact_name, last_message_at, unread_count)
            VALUES
                (:wa_id, :phone, :customer_id, :contact_name, :last_message_at, :unread_count)
        ");
        $insert->execute([
            ':wa_id' => $waId,
            ':phone' => $phone,
            ':customer_id' => $customerId ?: null,
            ':contact_name' => $contactName,
            ':last_message_at' => $lastMessageAt,
            ':unread_count' => $incrementUnread ? 1 : 0
        ]);

        return (int)$pdo->lastInsertId();
    }

    public static function touchConversation(PDO $pdo, $conversationId, $lastMessageAt = null)
    {
        $stmt = $pdo->prepare("
            UPDATE whatsapp_conversations
            SET last_message_at = :last_message_at
            WHERE id = :id
        ");
        $stmt->execute([
            ':last_message_at' => $lastMessageAt ?: date('Y-m-d H:i:s'),
            ':id' => (int)$conversationId
        ]);
    }

    public static function findCustomerIdByPhone(PDO $pdo, $waId, $phone = null)
    {
        $numbers = array_values(array_unique(array_filter([
            self::normalizePhoneNumber($waId),
            self::normalizePhoneNumber($phone),
            '+' . self::normalizePhoneNumber($waId),
            '+' . self::normalizePhoneNumber($phone),
        ])));

        foreach (['clients', 'customers', 'utilisateur'] as $table) {
            if (!self::tableExists($pdo, $table)) {
                continue;
            }

            foreach (['phone', 'telephone', 'tel', 'whatsapp', 'telephone_whatsapp'] as $column) {
                if (!self::columnExists($pdo, $table, $column)) {
                    continue;
                }

                $stmt = $pdo->query("SELECT id, `$column` AS phone_value FROM `$table` WHERE `$column` IS NOT NULL");
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $candidate = self::normalizePhoneNumber($row['phone_value'] ?? '');
                    if ($candidate !== '' && in_array($candidate, $numbers, true)) {
                        return (int)$row['id'];
                    }
                }
            }
        }

        return null;
    }

    private static function migrateLegacyMessages(PDO $pdo)
    {
        if (!self::columnExists($pdo, 'whatsapp_messages', 'conversation_id')) {
            $pdo->exec("ALTER TABLE whatsapp_messages ADD conversation_id INT NULL AFTER id");
        }

        if (!self::columnExists($pdo, 'whatsapp_messages', 'direction')) {
            $pdo->exec("ALTER TABLE whatsapp_messages ADD direction ENUM('in','out') NOT NULL DEFAULT 'in' AFTER wa_message_id");
        }

        foreach (['message_type' => 'VARCHAR(50) NULL', 'message_body' => 'TEXT NULL', 'status' => 'VARCHAR(50) NULL', 'raw_payload' => 'LONGTEXT NULL', 'created_at' => 'DATETIME DEFAULT CURRENT_TIMESTAMP'] as $column => $definition) {
            if (!self::columnExists($pdo, 'whatsapp_messages', $column)) {
                $pdo->exec("ALTER TABLE whatsapp_messages ADD `$column` $definition");
            }
        }

        if (self::columnExists($pdo, 'whatsapp_messages', 'wa_id')) {
            $rows = $pdo->query("
                SELECT id, wa_id, phone, contact_name, created_at
                FROM whatsapp_messages
                WHERE conversation_id IS NULL
                ORDER BY id ASC
            ")->fetchAll(PDO::FETCH_ASSOC);

            foreach ($rows as $row) {
                $conversationId = self::upsertConversation(
                    $pdo,
                    $row['wa_id'] ?? $row['phone'] ?? '',
                    $row['phone'] ?? null,
                    $row['contact_name'] ?? null,
                    false,
                    $row['created_at'] ?? null
                );
                $stmt = $pdo->prepare("UPDATE whatsapp_messages SET conversation_id = :conversation_id WHERE id = :id");
                $stmt->execute([
                    ':conversation_id' => $conversationId,
                    ':id' => (int)$row['id']
                ]);
            }
        }

        try {
            $pdo->exec("ALTER TABLE whatsapp_messages MODIFY conversation_id INT NOT NULL");
        } catch (Throwable $e) {
            self::log('WhatsApp migration warning', ['error' => $e->getMessage()]);
        }
    }

    private static function tableExists(PDO $pdo, $table)
    {
        $stmt = $pdo->prepare("
            SELECT COUNT(*)
            FROM INFORMATION_SCHEMA.TABLES
            WHERE TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = :table
        ");
        $stmt->execute([':table' => $table]);
        return ((int)$stmt->fetchColumn()) > 0;
    }

    private static function columnExists(PDO $pdo, $table, $column)
    {
        $stmt = $pdo->prepare("
            SELECT COUNT(*)
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = :table
            AND COLUMN_NAME = :column
        ");
        $stmt->execute([
            ':table' => $table,
            ':column' => $column
        ]);
        return ((int)$stmt->fetchColumn()) > 0;
    }

    private static function ensureIndex(PDO $pdo, $table, $index, $column)
    {
        $stmt = $pdo->prepare("
            SELECT COUNT(*)
            FROM INFORMATION_SCHEMA.STATISTICS
            WHERE TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = :table
            AND INDEX_NAME = :index_name
        ");
        $stmt->execute([
            ':table' => $table,
            ':index_name' => $index
        ]);

        if ((int)$stmt->fetchColumn() === 0) {
            $pdo->exec("CREATE INDEX `$index` ON `$table` (`$column`)");
        }
    }
}
