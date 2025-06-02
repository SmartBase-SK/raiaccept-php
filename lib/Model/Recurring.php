<?php
/**
 * Recurring
 *
 * PHP version 7.4
 *
 * @category Class
 */

namespace Raiaccept\RaiacceptApiClient\Model;

class Recurring {
    public string $cardToken;
    public string $customerReference;
    public string $recurringModel;

    public static function fromArray(array $data): self {
        $instance = new self();
        if (array_key_exists('cardToken', $data)) {
            $instance->cardToken = $data['cardToken'];
        }
        if (array_key_exists('customerReference', $data)) {
            $instance->customerReference = $data['customerReference'];
        }
        $instance->recurringModel = $data['recurringModel'] ?? '';
        return $instance;
    }
}
