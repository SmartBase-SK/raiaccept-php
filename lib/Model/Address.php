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
    public string $addressStreet1;
    public string $addressStreet2;
    public string $addressStreet3;
    public string $city;
    public string $country;
    public string $firstName;
    public string $lastName;
    public string $postalCode;
    public string $state;

    public static function fromArray(array $data): self {
        $instance = new self();
        $instance->addressStreet1 = $data['addressStreet1'] ?? '';
        $instance->addressStreet2 = $data['addressStreet2'] ?? '';
        $instance->addressStreet3 = $data['addressStreet3'] ?? '';
        $instance->city = $data['city'] ?? '';
        $instance->country = $data['country'] ?? '';
        $instance->firstName = $data['firstName'] ?? '';
        $instance->lastName = $data['lastName'] ?? '';
        $instance->postalCode = $data['postalCode'] ?? '';
        $instance->state = $data['state'] ?? '';
        return $instance;
    }
}
