<?php

class OrderAmountService
{
    private $config;

    public function __construct()
    {
        $this->config = include CONFIG . 'payment.php';
    }

    public function resolveCheckoutTotals(array $items, $zoneId)
    {
        $subtotal = ohnous_get_items_total($items);
        $deliveryPrice = ohnous_get_delivery_price_for_zone((int)$zoneId);

        if ($deliveryPrice === null) {
            throw new RuntimeException("Le prix de livraison n'est pas configuré pour cette zone.");
        }

        return [
            'subtotal' => (float)$subtotal,
            'delivery_price' => (float)$deliveryPrice,
            'total' => (float)$subtotal + (float)$deliveryPrice,
            'currency' => (string)($this->config['currency'] ?? 'CDF'),
            'display_currency' => (string)($this->config['display_currency'] ?? ($this->config['currency'] ?? 'CDF')),
        ];
    }
}
