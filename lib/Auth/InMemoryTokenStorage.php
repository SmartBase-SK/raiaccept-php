<?php

namespace Raiaccept\RaiacceptApiClient\Auth;

use Raiaccept\RaiacceptApiClient\Model\AuthTokens;

class InMemoryTokenStorage implements TokenStorage
{
    private ?AuthTokens $tokens = null;

    public function get(): ?AuthTokens
    {
        return $this->tokens;
    }

    public function save(AuthTokens $tokens): void
    {
        $this->tokens = $tokens;
    }

    public function clear(): void
    {
        $this->tokens = null;
    }
}
