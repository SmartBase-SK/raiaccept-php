<?php

namespace Raiaccept\RaiacceptApiClient\Auth;

use Raiaccept\RaiacceptApiClient\Model\IntegrationContext;

class AuthTokenManager
{
    private AuthClient $authClient;
    private TokenStorage $storage;
    private int $accessTokenBufferSeconds;

    public function __construct(
        AuthClient $authClient,
        TokenStorage $storage,
        int $accessTokenBufferSeconds = 60
    ) {
        $this->authClient = $authClient;
        $this->storage = $storage;
        $this->accessTokenBufferSeconds = $accessTokenBufferSeconds;
    }

    public function clear(): void
    {
        $this->storage->clear();
    }

    public function logout(): bool
    {
        $stored = $this->storage->get();
        $success = true;

        if ($stored !== null && $stored->refreshToken !== '') {
            $success = $this->authClient->logout($stored->refreshToken);
        }

        $this->storage->clear();

        return $success;
    }

    public function getAccessToken(
        string $username,
        string $password,
        IntegrationContext $integrationContext
    ): ?string {
        try {
            $now = time();
            $stored = $this->storage->get();

            if ($stored !== null && ! $stored->matchesIdentity($username, $integrationContext)) {
                $this->storage->clear();
                $stored = null;
            }

            if ($stored !== null && $stored->isAccessTokenValid($now, $this->accessTokenBufferSeconds)) {
                return $stored->accessToken;
            }

            if ($stored !== null && $stored->isRefreshTokenValid($now)) {
                try {
                    $refreshResponse = $this->authClient->refresh($stored->refreshToken, $integrationContext);
                    $tokens = $stored->withRefreshedAccess($refreshResponse);
                    $this->storage->save($tokens);

                    return $tokens->accessToken;
                } catch (\Throwable $e) {
                    $this->storage->clear();
                }
            }

            $tokens = $this->authClient->login($username, $password, $integrationContext);
            $tokens->bindIdentity($username, $integrationContext);
            $this->storage->save($tokens);

            return $tokens->accessToken;
        } catch (\Throwable $e) {
            return null;
        }
    }
}
