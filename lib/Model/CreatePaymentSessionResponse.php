<?php
/**
 * CreatePaymentSessionResponse
 *
 * PHP version 7.4
 *
 * @category Class
 */

namespace Raiaccept\RaiacceptApiClient\Model;

class CreatePaymentSessionResponse {
    public string $sessionId;
    public string $paymentRedirectURL;

    public static function fromArray(array $data): self {
        $instance = new self();
        $instance->sessionId = $data['sessionId'] ?? '';
        $instance->paymentRedirectURL = $data['paymentRedirectURL'] ?? '';
        return $instance;
    }

    public function getPaymentRedirectURL(): string
    {
        return $this->paymentRedirectURL;
    }
}
