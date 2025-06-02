<?php
/**
 * RefundRequest
 *
 * PHP version 7.4
 *
 * @category Class
 */

namespace Raiaccept\RaiacceptApiClient\Model;

class RefundRequest {
    public float $amount;
    public string $currency;

    public static function fromArray(array $data): self {
        $instance = new self();
        $instance->amount = $data['amount'] ?? '';
        $instance->currency = $data['currency'] ?? '';
        return $instance;
    }
}
