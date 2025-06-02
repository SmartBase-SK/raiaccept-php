<?php
/**
 * Urls
 *
 * PHP version 7.4
 *
 * @category Class
 */

namespace Raiaccept\RaiacceptApiClient\Model;

class Urls {
    public string $cancelUrl;
    public string $failUrl;
    public string $successUrl;
    public ?string $notificationUrl;

    public static function fromArray(array $data): self {
        $instance = new self();
        $instance->cancelUrl = $data['cancelUrl'] ?? '';
        $instance->failUrl = $data['failUrl'] ?? '';
        $instance->successUrl = $data['successUrl'] ?? '';
        $instance->notificationUrl = $data['notificationUrl'] ?? '';
        return $instance;
    }
}
