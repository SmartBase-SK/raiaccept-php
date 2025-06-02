<?php
/**
 * CreateOrderEntryRequest
 *
 * PHP version 7.4
 *
 * @category Class
 */

namespace Raiaccept\RaiacceptApiClient\Model;

class CreateOrderEntryRequest {
    public ?Address $billingAddress;
    public ?Consumer $consumer;
    public Invoice $invoice;
    public string $paymentMethodPreference;
    public ?Address $shippingAddress;
    public Urls $urls;
    public string $linkId;
    public ?Recurring $recurring;

    public static function fromArray(array $data): self {
        $instance = new self();
        $instance->billingAddress = $data['billingAddress'] ?? null;
        $instance->shippingAddress = $data['shippingAddress'] ?? null;
        $instance->consumer = $data['consumer'] ?? null;
        $instance->invoice = $data['invoice'] ?? [];
        $instance->urls = $data['urls'] ?? [];
        $instance->recurring = $data['recurring'] ?? null;
        $instance->paymentMethodPreference = $data['paymentMethodPreference'] ?? '';
        $instance->linkId = $data['linkId'] ?? '';
        return $instance;
    }
}
