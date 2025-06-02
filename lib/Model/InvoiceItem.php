<?php
/**
 * InvoiceItem
 *
 * PHP version 7.4
 *
 * @category Class
 */

namespace Raiaccept\RaiacceptApiClient\Model;

class InvoiceItem {
    public string $description;
    public int $numberOfItems;
    public float $price;

    public static function fromArray(array $data): self {
        $instance = new self();
        $instance->description = $data['description'] ?? '';
        $instance->numberOfItems = $data['numberOfItems'] ?? 0;
        $instance->price = $data['price'] ?? 0.0;
        return $instance;
    }
}
