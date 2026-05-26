<?php

namespace Raiaccept\RaiacceptApiClient;

/**
 * OrderInput field sanitization profiles aligned with RaiAccept apiUser-api OpenAPI schema.
 *
 * @see https://trapi.raiaccept.com/swagger-ui/index.html?urls.primaryName=apiUser-api#/order-controller/createOrder
 */
class OrderFieldFormat
{
    public const PERSON_NAME = 'person_name';
    public const ADDRESS_LINE = 'address_line';
    public const POSTAL_CODE = 'postal_code';
    public const EMAIL = 'email';
    public const MERCHANT_REFERENCE = 'merchant_reference';
    public const FREE_TEXT = 'free_text';

    /**
     * Swagger validation patterns keyed by format constant.
     */
    public const VALIDATION_PATTERNS = [
        self::PERSON_NAME => '/^[\p{L}`\' .-]*$/u',
        self::ADDRESS_LINE => '/^[\p{L}\d`\'\(\) .,#\/-]*$/u',
        self::POSTAL_CODE => '/^[a-zA-Z\d -]*$/',
        self::EMAIL => '/^[a-zA-Z0-9!#$%&\'*+\/=?^_`{|}~-]+(?:\.[a-zA-Z0-9!#$%&\'*+\/=?^_`{|}~-]+)*@(?:[a-zA-Z0-9](?:[a-zA-Z0-9-]*[a-zA-Z0-9])?\.)+[a-zA-Z0-9](?:[a-zA-Z0-9-]*[a-zA-Z0-9])?$/',
        self::MERCHANT_REFERENCE => '/^[a-zA-Z0-9_-]*$/',
    ];

    public const PHONE_VALIDATION_PATTERN = '/^$|^(\+|00)[1-9]\d{1,3}\d{6,11}$|^\d{8,16}$/';
}
