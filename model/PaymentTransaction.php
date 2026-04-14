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
        insert_bdd($this->bdd, $this->table, $data);
        return (int)$this->bdd->lastInsertId();
    }

    public function updateById($id, array $data)
    {
        return update_bdd($this->bdd, $this->table, $data, "id = '" . (int)$id . "'");
    }
}
