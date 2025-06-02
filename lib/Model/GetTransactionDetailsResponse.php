<?php
/**
 * GetTransactionDetailsResponse
 *
 * PHP version 7.4
 *
 * @category Class
 */

 namespace Raiaccept\RaiacceptApiClient\Model;

class GetTransactionDetailsResponse
{
    public Transaction $transaction;
    public Merchant $merchant;
    public GetTransactionDetailsResponseOrder $order;
    public Card $card;

    public static function fromArray(array $data): self
    {
        $obj = new self();
        $obj->transaction = Transaction::fromArray($data['transaction']);
        $obj->merchant = Merchant::fromArray($data['merchant']);
        $obj->order = GetTransactionDetailsResponseOrder::fromArray($data['order']);
        $obj->card = Card::fromArray($data['card']);
        return $obj;
    }

    public function getStatus(): string
    {
        if ( is_null($this->transaction) ) {
            return "";
        }
        return $this->transaction->getStatus();
    }

    public function getTransactionType(): string
    {
        if ( is_null($this->transaction) ) {
            return "";
        }
        return $this->transaction->getTransactionType();
    }

    public function getCardObj(): Card
    {
        return $this->card;
    }
}
