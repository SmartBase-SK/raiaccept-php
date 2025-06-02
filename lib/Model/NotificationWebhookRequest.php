<?php
/**
 * NotificationWebhookRequest
 *
 * PHP version 7.4
 *
 * @category Class
 */

namespace Raiaccept\RaiacceptApiClient\Model;

class NotificationWebhookRequest {
    public CreateOrderEntryResponse $order;
    public Transaction $transaction;
    public Merchant $merchant;
    public Card $card;

    public static function fromArray(array $data): self {
        $instance = new self();
        $instance->order = CreateOrderEntryResponse::fromArray($data['order'] ?? []);
        $instance->transaction = Transaction::fromArray($data['transaction'] ?? []);
        $instance->merchant = Merchant::fromArray($data['merchant'] ?? []);
        $instance->card = Card::fromArray($data['card'] ?? []);
        return $instance;
    }

    public function getOrderIdentification(): string
    {
        if ( is_null($this->order) ) {
            return "";
        }
        return $this->order->getOrderIdentification();
    }

    public function getTransactionId(): string
    {
        if ( is_null($this->transaction) ) {
            return "";
        }
        return $this->transaction->getTransactionId();
    }

    public function getMerchantOrderReference(): string
    {
        if ( is_null($this->order) ) {
            return "";
        }
        return $this->order->getMerchantOrderReference();
    }
}