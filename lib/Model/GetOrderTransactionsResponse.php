<?php
/**
 * GetOrderTransactionsResponse
 *
 * PHP version 7.4
 *
 * @category Class
 */

namespace Raiaccept\RaiacceptApiClient\Model;

class GetOrderTransactionsResponse {
    /** @var Transaction[] */
    public array $transactions = [];


    public static function fromArray(array $data): self {
        $instance = new self();
        foreach ($data as $transaction) {
            $instance->transactions[] = Transaction::fromArray($transaction);
        }
        return $instance;
    }

    public function getTransactions(): array
    {
        return $this->transactions;
    }
}