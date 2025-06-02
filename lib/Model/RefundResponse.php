<?php
/**
 * RefundResponse
 *
 * PHP version 7.4
 *
 * @category Class
 */

namespace Raiaccept\RaiacceptApiClient\Model;

class RefundResponse {
    public string $transactionId;

    public static function fromArray(array $data): self {
        $instance = new self();
        $instance->transactionId = $data['transactionId'] ?? '';
        return $instance;
    }

    public function getTransactionId(): string
    {
        return $this->transactionId;
    }
}
