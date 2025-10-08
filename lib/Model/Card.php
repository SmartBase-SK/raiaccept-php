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
    public string $maskedCardNumber;
    public string $cardHolderName;
    public string $type;
    public string $issuerCountry;
    public string $cardToken;
    public bool $cardSavingApproved;
    public string $expiryMonth;
    public string $expiryYear;

    public static function fromArray(array $data): self
    {
        $obj = new self();
        $obj->maskedCardNumber = $data['maskedCardNumber'];
        $obj->cardHolderName = $data['cardHolderName'] ?? '';
        $obj->type = $data['type'];
        $obj->issuerCountry = $data['issuerCountry'];
        $obj->cardToken = $data['cardToken'];
        $obj->cardSavingApproved = $data['cardSavingApproved'] ?? false;
        $obj->expiryMonth = $data['expiryMonth'] ?? '';
        $obj->expiryYear = $data['expiryYear'] ?? '';
        return $obj;
    }

    public function getCardToken(): string
    {
        return $this->cardToken;
    }

    public function getExpiryMonth(): string
    {
        return $this->expiryMonth;
    }

    public function getExpiryYear(): string
    {
        return $this->expiryYear;
    }

    public function getCardSavingApproved(): string
    {
        return $this->cardSavingApproved;
    }

    public function getMaskedCardNumber(): string
    {
        return $this->maskedCardNumber;
    }

    public function getType(): string
    {
        return $this->type;
    }
}
