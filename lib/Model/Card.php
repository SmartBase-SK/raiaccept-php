<?php
/**
 * Card
 *
 * PHP version 7.4
 *
 * @category Class
 */

 namespace Raiaccept\RaiacceptApiClient\Model;

class Card
{
    public ?string $maskedCardNumber;
    public ?string $cardHolderName;
    public ?string $type;
    public ?string $issuerCountry;
    public ?string $cardToken;
    public ?bool $cardSavingApproved;
    public ?string $expiryMonth;
    public ?string $expiryYear;

    public static function fromArray(array $data): self
    {
        $obj = new self();
        $obj->maskedCardNumber = $data['maskedCardNumber'] ?? null;
        $obj->cardHolderName = $data['cardHolderName'] ?? null;
        $obj->type = $data['type'] ?? null;
        $obj->issuerCountry = $data['issuerCountry'] ?? null;
        $obj->cardToken = $data['cardToken'] ?? null;
        $obj->cardSavingApproved = $data['cardSavingApproved'] ?? null;
        $obj->expiryMonth = $data['expiryMonth'] ?? null;
        $obj->expiryYear = $data['expiryYear'] ?? null;
        return $obj;
    }

    public function getCardToken(): ?string
    {
        return $this->cardToken;
    }

    public function getExpiryMonth(): ?string
    {
        return $this->expiryMonth;
    }

    public function getExpiryYear(): ?string
    {
        return $this->expiryYear;
    }

    public function getCardSavingApproved(): ?bool
    {
        return $this->cardSavingApproved;
    }

    public function getMaskedCardNumber(): ?string
    {
        return $this->maskedCardNumber;
    }

    public function getType(): ?string
    {
        return $this->type;
    }
}
