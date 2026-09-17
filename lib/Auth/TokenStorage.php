<?php

namespace Raiaccept\RaiacceptApiClient\Auth;

use Raiaccept\RaiacceptApiClient\Model\AuthTokens;

interface TokenStorage
{
    public function get(): ?AuthTokens;

    public function save(AuthTokens $tokens): void;

    public function clear(): void;
}
