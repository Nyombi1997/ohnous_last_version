<?php

class AdminAccessToken
{
    private $bdd;
    private $table = 'admin_access_tokens';

    public function __construct(PDO $bdd)
    {
        $this->bdd = $bdd;
    }

    public function create($adminId, $redirectPath = '/admin-admins?invited=1', $expiresAt = null)
    {
        $token = bin2hex(random_bytes(24));
        $expireAt = $expiresAt ?: (new DateTime('+72 hours'))->format('Y-m-d H:i:s');

        insert_bdd($this->bdd, $this->table, [
            'admin_id' => (int)$adminId,
            'token' => $token,
            'redirect_path' => $redirectPath,
            'expire_at' => $expireAt,
        ]);

        return [
            'token' => $token,
            'expire_at' => $expireAt,
        ];
    }

    public function consumeValidToken($tokenValue)
    {
        $stmt = $this->bdd->prepare("
            SELECT *
            FROM {$this->table}
            WHERE token = :token
            AND used_at IS NULL
            AND expire_at >= NOW()
            ORDER BY id DESC
            LIMIT 1
        ");
        $stmt->execute([':token' => $tokenValue]);
        $token = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$token) {
            return false;
        }

        update_bdd($this->bdd, $this->table, [
            'used_at' => date('Y-m-d H:i:s')
        ], "id = '" . (int)$token['id'] . "'");

        return $token;
    }
}
