<?php
/**
 * GetTransactionDetailsResponseOrder
 *
 * PHP version 7.4
 *
 * @category Class
 */

namespace Raiaccept\RaiacceptApiClient\Model;

class GetTransactionDetailsResponseOrder
{
    public string $orderIdentification;
    public Invoice $invoice;

    public static function fromArray(array $data): self
    {
        $obj = new self();
        $obj->orderIdentification = $data['orderIdentification'];
        $obj->invoice = Invoice::fromArray($data['invoice']);
        return $obj;
    }
}
