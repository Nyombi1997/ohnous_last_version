<?php

class PaymentTransaction
{
    private $bdd;
    private $table = 'payment_transactions';

    public function __construct(PDO $bdd)
    {
        $this->bdd = $bdd;
    }

    public function findByReference($reference)
    {
        $stmt = $this->bdd->prepare("SELECT * FROM {$this->table} WHERE reference = :reference LIMIT 1");
        $stmt->execute([':reference' => $reference]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findByOrderId($orderId)
    {
        $stmt = $this->bdd->prepare("SELECT * FROM {$this->table} WHERE order_id = :order_id ORDER BY id DESC LIMIT 1");
        $stmt->execute([':order_id' => (int)$orderId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create(array $data)
    {
        foreach (array_keys($data) as $column) {
            if (function_exists('ohnous_column_exists') && !ohnous_column_exists($this->table, $column)) {
                unset($data[$column]);
            }
        }

        insert_bdd($this->bdd, $this->table, $data);
        return (int)$this->bdd->lastInsertId();
    }

    public function updateById($id, array $data)
    {
        foreach (array_keys($data) as $column) {
            if (function_exists('ohnous_column_exists') && !ohnous_column_exists($this->table, $column)) {
                unset($data[$column]);
            }
        }

        if (empty($data)) {
            return false;
        }

        return update_bdd($this->bdd, $this->table, $data, "id = '" . (int)$id . "'");
    }

    public function search(array $filters = [], $limit = 100)
    {
        $where = [];
        $params = [];

        $q = trim((string)($filters['q'] ?? ''));
        if ($q !== '') {
            $where[] = "(pt.reference LIKE :q OR pt.freshpay_transaction_id LIKE :q OR pt.customer_number LIKE :q OR c.order_number LIKE :q OR c.nom_client LIKE :q OR c.telephone LIKE :q OR c.email LIKE :q)";
            $params[':q'] = '%' . $q . '%';
        }

        $status = trim((string)($filters['status'] ?? ''));
        if ($status !== '') {
            $statusGroups = [
                'pending' => ['pending', 'submitted'],
                'success' => ['success', 'successful', 'paid', 'completed'],
                'failed' => ['failed', 'error', 'rejected', 'refused', 'declined'],
                'cancelled' => ['cancelled', 'canceled'],
                'refunded' => ['refunded'],
            ];
            $values = $statusGroups[$status] ?? [$status];
            $placeholders = [];
            foreach ($values as $index => $value) {
                $key = ':status_' . $index;
                $placeholders[] = $key;
                $params[$key] = $value;
            }
            $where[] = "pt.trans_status IN (" . implode(', ', $placeholders) . ")";
        }

        $paymentMethod = trim((string)($filters['payment_method'] ?? ''));
        if ($paymentMethod !== '') {
            $where[] = "pt.payment_method = :payment_method";
            $params[':payment_method'] = $paymentMethod;
        }

        $client = trim((string)($filters['client'] ?? ''));
        if ($client !== '') {
            $where[] = "(c.nom_client LIKE :client OR c.telephone LIKE :client OR c.email LIKE :client)";
            $params[':client'] = '%' . $client . '%';
        }

        $storeId = (int)($filters['boutique_id'] ?? 0);
        if ($storeId > 0) {
            $where[] = "EXISTS (SELECT 1 FROM commande_articles ca_filter WHERE ca_filter.commande_id = c.id AND ca_filter.boutique_id = :boutique_id)";
            $params[':boutique_id'] = $storeId;
        }

        $dateFrom = trim((string)($filters['date_from'] ?? ''));
        if ($dateFrom !== '') {
            $where[] = "DATE(pt.created_at) >= :date_from";
            $params[':date_from'] = $dateFrom;
        }

        $dateTo = trim((string)($filters['date_to'] ?? ''));
        if ($dateTo !== '') {
            $where[] = "DATE(pt.created_at) <= :date_to";
            $params[':date_to'] = $dateTo;
        }

        $selectFee = function_exists('ohnous_column_exists') && ohnous_column_exists($this->table, 'payment_fee_amount')
            ? "pt.payment_fee_amount"
            : "GREATEST(pt.amount - COALESCE(c.sous_total, 0) - COALESCE(c.livraison_prix, 0), 0)";
        $selectProviderReference = function_exists('ohnous_column_exists') && ohnous_column_exists($this->table, 'provider_reference')
            ? "pt.provider_reference"
            : "pt.freshpay_transaction_id";
        $selectTransactionNumber = function_exists('ohnous_column_exists') && ohnous_column_exists($this->table, 'transaction_number')
            ? "pt.transaction_number"
            : "pt.financial_institution_id";

        $sql = "
            SELECT
                pt.*,
                " . $selectFee . " AS payment_fee_amount_resolved,
                " . $selectProviderReference . " AS provider_reference_resolved,
                " . $selectTransactionNumber . " AS transaction_number_resolved,
                c.order_number,
                c.client_type,
                c.client_id,
                c.nom_client,
                c.telephone,
                c.email,
                c.adresse,
                c.zone_nom,
                c.livraison_prix,
                c.sous_total,
                c.total AS order_total,
                c.statut AS order_status,
                c.date_ajout AS order_date,
                GROUP_CONCAT(DISTINCT b.nom ORDER BY b.nom SEPARATOR ', ') AS boutiques
            FROM {$this->table} pt
            LEFT JOIN commandes c ON c.id = pt.order_id
            LEFT JOIN commande_articles ca ON ca.commande_id = c.id
            LEFT JOIN boutiques b ON b.id = ca.boutique_id
        ";

        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $sql .= " GROUP BY pt.id ORDER BY pt.created_at DESC, pt.id DESC";

        if ($limit !== null) {
            $sql .= " LIMIT :limit";
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

    public function findDetailed($id)
    {
        $selectFee = function_exists('ohnous_column_exists') && ohnous_column_exists($this->table, 'payment_fee_amount')
            ? "pt.payment_fee_amount"
            : "GREATEST(pt.amount - COALESCE(c.sous_total, 0) - COALESCE(c.livraison_prix, 0), 0)";
        $selectProviderReference = function_exists('ohnous_column_exists') && ohnous_column_exists($this->table, 'provider_reference')
            ? "pt.provider_reference"
            : "pt.freshpay_transaction_id";
        $selectTransactionNumber = function_exists('ohnous_column_exists') && ohnous_column_exists($this->table, 'transaction_number')
            ? "pt.transaction_number"
            : "pt.financial_institution_id";

        $stmt = $this->bdd->prepare("
            SELECT
                pt.*,
                " . $selectFee . " AS payment_fee_amount_resolved,
                " . $selectProviderReference . " AS provider_reference_resolved,
                " . $selectTransactionNumber . " AS transaction_number_resolved,
                c.order_number,
                c.client_type,
                c.client_id,
                c.nom_client,
                c.telephone,
                c.email,
                c.adresse,
                c.zone_nom,
                c.livraison_prix,
                c.sous_total,
                c.total AS order_total,
                c.statut AS order_status,
                c.date_ajout AS order_date,
                GROUP_CONCAT(DISTINCT b.nom ORDER BY b.nom SEPARATOR ', ') AS boutiques
            FROM {$this->table} pt
            LEFT JOIN commandes c ON c.id = pt.order_id
            LEFT JOIN commande_articles ca ON ca.commande_id = c.id
            LEFT JOIN boutiques b ON b.id = ca.boutique_id
            WHERE pt.id = :id
            GROUP BY pt.id
            LIMIT 1
        ");
        $stmt->execute([':id' => (int)$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $row['articles'] = $this->getOrderItems((int)$row['order_id']);
            $row['client_profile'] = $this->getClientProfile((string)$row['client_type'], (int)$row['client_id']);
            return $row;
        }

        return null;
    }

    public function getOrderItems($orderId)
    {
        $stmt = $this->bdd->prepare("
            SELECT ca.*, b.nom AS boutique_nom, b.slug AS boutique_slug
            FROM commande_articles ca
            LEFT JOIN boutiques b ON b.id = ca.boutique_id
            WHERE ca.commande_id = :order_id
            ORDER BY ca.id ASC
        ");
        $stmt->execute([':order_id' => (int)$orderId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getClientProfile($clientType, $clientId)
    {
        if ($clientId <= 0) {
            return null;
        }

        if ($clientType === 'utilisateur') {
            $stmt = $this->bdd->prepare("SELECT id, nom, slug, profile FROM utilisateur WHERE id = :id LIMIT 1");
            $stmt->execute([':id' => $clientId]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        }

        if ($clientType === 'boutique') {
            $stmt = $this->bdd->prepare("SELECT id, nom, slug, profile FROM boutiques WHERE id = :id LIMIT 1");
            $stmt->execute([':id' => $clientId]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        }

        return null;
    }
}
