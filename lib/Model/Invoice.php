<?php
/**
 * Invoice
 *
 * PHP version 7.4
 *
 * @category Class
 */

namespace Raiaccept\RaiacceptApiClient\Model;

class Invoice {
    public float $amount;
    public string $currency;
    public string $description;
    /** @var InvoiceItem[] */
    public array $items = [];
    public string $merchantOrderReference;

    public static function fromArray(array $data): self {
        $instance = new self();
        $instance->amount = $data['amount'] ?? 0.0;
        $instance->currency = $data['currency'] ?? '';
        $instance->description = $data['description'] ?? '';
        $instance->merchantOrderReference = $data['merchantOrderReference'] ?? '';

        $items = $data['items'] ?? [];
        foreach( $items as $item ) {
            if (is_array($item)) {
                $instance->items[] = InvoiceItem::fromArray($item);
            } else {
                $instance->items[] = $item;
            }

        }

        return $instance;
    }

    public function getMerchantOrderReference(): string
    {
        return $this->merchantOrderReference;
    }
}
