<?php
/**
 * Transaction
 *
 * PHP version 7.4
 *
 * @category Class
 */

namespace Raiaccept\RaiacceptApiClient\Model;

class Transaction {
    public const TRANSACTION_TYPE_PURCHASE = 'PURCHASE';
    public const TRANSACTION_TYPE_REFUND = 'REFUND';

    public string $transactionId;
    public float $transactionAmount;
    public string $transactionCurrency;
    public bool $isProduction;
    public string $transactionType;
    public string $paymentMethod;
    public string $status;
    public string $statusCode;
    public string $statusMessage;
    public string $createdOn;
    public string $updatedOn;


    public static function fromArray(array $data): self {
        $instance = new self();
        $instance->transactionId = $data['transactionId'] ?? '';
        $instance->transactionAmount = $data['transactionAmount'] ?? '';
        $instance->transactionCurrency = $data['transactionCurrency'] ?? '';
        $instance->isProduction = $data['isProduction'] ?? '';
        $instance->transactionType = $data['transactionType'] ?? '';
        $instance->paymentMethod = $data['paymentMethod'] ?? '';
        $instance->status = $data['status'] ?? '';
        $instance->statusCode = $data['statusCode'] ?? '';
        $instance->statusMessage = $data['statusMessage'] ?? '';
        $instance->createdOn = $data['createdOn'] ?? '';
        $instance->updatedOn = $data['updatedOn'] ?? '';
        return $instance;
    }

    public function getTransactionId(): string
    {
        return $this->transactionId;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getTransactionType(): string
    {
        return $this->transactionType;
    }

    public function getTransactionAmount(): string
    {
        return $this->transactionAmount;
    }

    public function getTransactionCurrency(): string
    {
        return $this->transactionCurrency;
    }

    public function getCreatedOn(): string
    {
        return $this->createdOn;
    }
}