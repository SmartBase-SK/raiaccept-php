<?php
/**
 * GetOrderDetailsResponse
 *
 * PHP version 7.4
 *
 * @category Class
 */

namespace Raiaccept\RaiacceptApiClient\Model;

class GetOrderDetailsResponse extends CreateOrderEntryRequest {
    public string $status;

    public static function fromArray(array $data): self {
        $instance = new self();
        $instance->status = $data['status'] ?? '';
        return $instance;
    }

    public function getStatus(): string
    {
        return $this->status;
    }
}
