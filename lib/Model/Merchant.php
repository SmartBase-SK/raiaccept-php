<?php
/**
 * Merchant
 *
 * PHP version 7.4
 *
 * @category Class
 */

namespace Raiaccept\RaiacceptApiClient\Model;

class Merchant {
    public string $merchantAccountId;
    public string $statementDescriptorShortVersion;

    public static function fromArray(array $data): self {
        $instance = new self();
        $instance->merchantAccountId = $data['merchantAccountId'] ?? '';
        $instance->statementDescriptorShortVersion = $data['statementDescriptorShortVersion'] ?? '';
        return $instance;
    }
}
