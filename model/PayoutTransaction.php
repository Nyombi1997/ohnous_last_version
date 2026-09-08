<?php

class PayoutTransaction
{
    private $bdd;
    private $table = 'payout_transactions';

    public function __construct(PDO $bdd)
    {
        $this->bdd = $bdd;
    }

    public function create(array $data)
    {
        foreach (array_keys($data) as $column) {
            if (function_exists('ohnous_column_exists') && !ohnous_column_exists($this->table, $column)) unset($data[$column]);
        }
        insert_bdd($this->bdd, $this->table, $data);
        return (int)$this->bdd->lastInsertId();
    }

    public function updateById($id, array $data)
    {
        foreach (array_keys($data) as $column) {
            if (function_exists('ohnous_column_exists') && !ohnous_column_exists($this->table, $column)) unset($data[$column]);
        }
        if (!$data) return false;
        return update_bdd($this->bdd, $this->table, $data, "id = '" . (int)$id . "'");
    }

    public function findByReference($reference)
    {
        $stmt = $this->bdd->prepare("SELECT * FROM {$this->table} WHERE reference = :reference LIMIT 1");
        $stmt->execute([':reference' => $reference]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findById($id)
    {
        $stmt = $this->bdd->prepare("SELECT * FROM {$this->table} WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => (int)$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function search(array $filters = [], $limit = 100)
    {
        $where = [];
        $params = [];
        if (!empty($filters['boutique_id'])) {
            $where[] = 'EXISTS (SELECT 1 FROM boutique_payout_links l WHERE l.payout_id = payout_transactions.id AND l.boutique_id = :boutique_id)';
            $params[':boutique_id'] = (int)$filters['boutique_id'];
        }
        foreach (['status', 'operator'] as $field) {
            $value = trim((string)($filters[$field] ?? ''));
            if ($value !== '') {
                $where[] = "{$field} = :{$field}";
                $params[":{$field}"] = $value;
            }
        }
        $q = trim((string)($filters['q'] ?? ''));
        if ($q !== '') {
            $searchColumns = ['reference', 'beneficiary', 'phone_number', 'freshpay_reference', 'transaction_id'];
            $parts = [];
            foreach ($searchColumns as $index => $column) {
                $parts[] = $column . ' LIKE :q' . $index;
                $params[':q' . $index] = '%' . $q . '%';
            }
            $where[] = '(' . implode(' OR ', $parts) . ')';
        }
        if (!empty($filters['date_from'])) {
            $where[] = 'DATE(created_at) >= :date_from';
            $params[':date_from'] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $where[] = 'DATE(created_at) <= :date_to';
            $params[':date_to'] = $filters['date_to'];
        }
        $sql = "SELECT * FROM {$this->table}" . ($where ? ' WHERE ' . implode(' AND ', $where) : '') . ' ORDER BY created_at DESC, id DESC';
        if ($limit !== null) {
            $sql .= ' LIMIT :limit';
        }
        $stmt = $this->bdd->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        if ($limit !== null) {
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findByAnyReference($reference)
    {
        $stmt = $this->bdd->prepare("SELECT * FROM {$this->table} WHERE reference = :reference OR freshpay_reference = :reference OR transaction_id = :reference OR operator_reference = :reference LIMIT 1");
        $stmt->execute([':reference' => trim((string)$reference)]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function boutiques()
    {
        return $this->bdd->query('SELECT id, nom FROM boutiques ORDER BY nom, id')->fetchAll(PDO::FETCH_ASSOC);
    }

    public function boutiqueContext($id)
    {
        $stmt = $this->bdd->prepare('SELECT * FROM boutique_payout_profiles WHERE boutique_id = ?');
        $stmt->execute([(int)$id]);
        $profile = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
$stmt = $this->bdd->prepare('SELECT p.phone_number, p.operator, p.beneficiary, r.merchant_recipient_id, r.kyc_reference, r.recipient_id AS existing_recipient_id FROM payout_transactions p INNER JOIN boutique_payout_links l ON l.payout_id = p.id LEFT JOIN moko_recipients r ON r.recipient_id = CONVERT(p.moko_recipient_id USING utf8mb4) COLLATE utf8mb4_general_ci WHERE l.boutique_id = ? ORDER BY p.created_at DESC, p.id DESC');
        $stmt->execute([(int)$id]);
        $phones = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if (!isset($phones[$row['phone_number']])) $phones[$row['phone_number']] = $row;
        }
        if ($profile && !isset($phones[$profile['phone_number']])) $phones[$profile['phone_number']] = $profile;
        return ['profile'=>$profile, 'phones'=>array_values($phones)];
    }

    public function statistics($boutiqueId = 0)
    {
        $sql = "SELECT COUNT(*) AS total,
                       COALESCE(SUM(amount), 0) AS total_amount,
                       SUM(CASE WHEN LOWER(status) IN ('success','successful','paid','completed') THEN 1 ELSE 0 END) AS successful,
                       SUM(CASE WHEN LOWER(status) IN ('failed','error','expired','cancelled','canceled','rejected','refused','declined','send_rejected') THEN 1 ELSE 0 END) AS failed,
                       SUM(CASE WHEN LOWER(status) NOT IN ('success','successful','paid','completed','failed','error','expired','cancelled','canceled','rejected','refused','declined','send_rejected') THEN 1 ELSE 0 END) AS pending
                FROM {$this->table}";
        if ($boutiqueId) $sql .= ' WHERE EXISTS (SELECT 1 FROM boutique_payout_links l WHERE l.payout_id = payout_transactions.id AND l.boutique_id = :boutique_id)';
        $stmt = $this->bdd->prepare($sql);
        $stmt->execute($boutiqueId ? [':boutique_id'=>(int)$boutiqueId] : []);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }

    public function amountsByCurrency($boutiqueId = 0)
    {
        $sql = "SELECT currency, SUM(amount) AS requested, SUM(CASE WHEN LOWER(status) IN ('success','successful','paid','completed') THEN amount ELSE 0 END) AS paid FROM payout_transactions";
        if ($boutiqueId) $sql .= ' WHERE EXISTS (SELECT 1 FROM boutique_payout_links l WHERE l.payout_id=payout_transactions.id AND l.boutique_id=?)';
        $stmt = $this->bdd->prepare($sql.' GROUP BY currency ORDER BY currency');
        $stmt->execute($boutiqueId ? [(int)$boutiqueId] : []);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addStatusEvent($payoutId, $status, $description = '', $source = 'system', array $payload = [])
    {
        if (!function_exists('ohnous_table_exists') || !ohnous_table_exists('payout_status_history')) return false;
        $stmt = $this->bdd->prepare("INSERT INTO payout_status_history (payout_id,status,description,source,payload,created_at) VALUES (:id,:status,:description,:source,:payload,NOW())");
        return $stmt->execute([':id'=>(int)$payoutId,':status'=>(string)$status,':description'=>(string)$description,':source'=>(string)$source,':payload'=>$payload ? json_encode($payload, JSON_UNESCAPED_UNICODE) : null]);
    }

    public function getStatusHistory($payoutId)
    {
        if (!function_exists('ohnous_table_exists') || !ohnous_table_exists('payout_status_history')) return [];
        $stmt = $this->bdd->prepare('SELECT * FROM payout_status_history WHERE payout_id = :id ORDER BY created_at ASC, id ASC');
        $stmt->execute([':id'=>(int)$payoutId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
