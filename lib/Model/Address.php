<?php
/**
 * Address
 *
 * PHP version 7.4
 *
 * @category Class
 */

namespace Raiaccept\RaiacceptApiClient\Model;

class Address {
    public ?string $addressStreet1;
    public ?string $addressStreet2;
    public ?string $addressStreet3;
    public ?string $city;
    public ?string $country;
    public ?string $firstName;
    public ?string $lastName;
    public ?string $postalCode;
    public ?string $state;

    public static function fromArray(array $data): self {
        $instance = new self();
        $instance->addressStreet1 = $data['addressStreet1'] ?? null;
        $instance->addressStreet2 = $data['addressStreet2'] ?? null;
        $instance->addressStreet3 = $data['addressStreet3'] ?? null;
        $instance->city = $data['city'] ?? null;
        $instance->country = $data['country'] ?? null;
        $instance->firstName = $data['firstName'] ?? null;
        $instance->lastName = $data['lastName'] ?? null;
        $instance->postalCode = $data['postalCode'] ?? null;
        $instance->state = $data['state'] ?? null;
        return $instance;
    }
}
