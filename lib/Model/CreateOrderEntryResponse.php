<?php
/**
 * CreateOrderEntryResponse
 *
 * PHP version 7.4
 *
 * @category Class
 */

namespace Raiaccept\RaiacceptApiClient\Model;

class CreateOrderEntryResponse {
    public ?Address $billingAddress;
    public ?Consumer $consumer;
    public Invoice $invoice;
    public string $paymentMethodPreference;
    public ?Address $shippingAddress;
    public Urls $urls;
    public string $linkId;
    public ?Recurring $recurring;
    public string $orderIdentification;
    public Merchant $merchant;
    public string $createdOn; // ISO 8601 timestamp
    public bool $isProduction;

    public static function fromArray(array $data): self {
        $instance = new self();
        $instance->billingAddress = Address::fromArray($data['billingAddress'] ?? []);
        $instance->shippingAddress = Address::fromArray($data['shippingAddress'] ?? []);
        $instance->consumer = Consumer::fromArray($data['consumer'] ?? []);
        $instance->invoice = Invoice::fromArray($data['invoice'] ?? []);
        $instance->urls = Urls::fromArray($data['urls'] ?? []);
        $instance->recurring = Recurring::fromArray($data['recurring'] ?? []);
        $instance->paymentMethodPreference = $data['paymentMethodPreference'] ?? '';
        $instance->linkId = $data['linkId'] ?? '';
        $instance->orderIdentification = $data['orderIdentification'] ?? '';
        $instance->merchant = Merchant::fromArray($data['merchant'] ?? []);
        $instance->createdOn = $data['createdOn'] ?? '';
        $instance->isProduction = $data['isProduction'] ?? false;
        return $instance;
    }

    public function getOrderIdentification(): string
    {
        return $this->orderIdentification;
    }

    public function getMerchantOrderReference(): string
    {
        if ( is_null($this->invoice) ) {
            return "";
        }
        return $this->invoice->getMerchantOrderReference();
    }
}
