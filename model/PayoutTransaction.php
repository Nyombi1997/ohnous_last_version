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
        insert_bdd($this->bdd, $this->table, $data);
        return (int)$this->bdd->lastInsertId();
    }

    public function updateById($id, array $data)
    {
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
        foreach (['status', 'operator'] as $field) {
            $value = trim((string)($filters[$field] ?? ''));
            if ($value !== '') {
                $where[] = "{$field} = :{$field}";
                $params[":{$field}"] = $value;
            }
        }
        $q = trim((string)($filters['q'] ?? ''));
        if ($q !== '') {
            $where[] = '(reference LIKE :q OR beneficiary LIKE :q OR phone_number LIKE :q OR freshpay_reference LIKE :q)';
            $params[':q'] = '%' . $q . '%';
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
}
