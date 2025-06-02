<?php
/**
 * Consumer
 *
 * PHP version 7.4
 *
 * @category Class
 */

namespace Raiaccept\RaiacceptApiClient\Model;

class Consumer {
    public string $email;
    public string $firstName;
    public string $ipAddress;
    public string $lastName;
    public string $mobilePhone;
    public string $phone;
    public string $workPhone;

    public static function fromArray(array $data): self {
        $instance = new self();
        $instance->email = $data['email'] ?? '';
        $instance->firstName = $data['firstName'] ?? '';
        $instance->ipAddress = $data['ipAddress'] ?? '';
        $instance->lastName = $data['lastName'] ?? '';
        $instance->mobilePhone = $data['mobilePhone'] ?? '';
        $instance->phone = $data['phone'] ?? '';
        $instance->workPhone = $data['workPhone'] ?? '';
        return $instance;
    }
}