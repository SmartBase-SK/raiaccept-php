<?php

namespace Raiaccept\RaiacceptApiClient\Model;

class AuthTokens
{
    public string $accessToken;
    public int $accessTokenExpiresAt;
    public string $refreshToken;
    public int $refreshTokenExpiresAt;

    public static function fromLoginResponse(LoginResponse $response, ?int $now = null): self
    {
        $now = $now ?? time();
        $obj = new self();
        $obj->accessToken = $response->accessToken;
        $obj->accessTokenExpiresAt = $now + $response->accessTokenExpiresIn;
        $obj->refreshToken = $response->refreshToken;
        $obj->refreshTokenExpiresAt = $now + $response->refreshTokenExpiresIn;

        return $obj;
    }

    public function withRefreshedAccess(RefreshResponse $response, ?int $now = null): self
    {
        $now = $now ?? time();
        $obj = new self();
        $obj->accessToken = $response->accessToken;
        $obj->accessTokenExpiresAt = $now + $response->accessTokenExpiresIn;
        $obj->refreshToken = $this->refreshToken;
        $obj->refreshTokenExpiresAt = $this->refreshTokenExpiresAt;

        return $obj;
    }

    public function isAccessTokenValid(?int $now = null, int $bufferSeconds = 60): bool
    {
        $now = $now ?? time();

        return ($this->accessTokenExpiresAt - $bufferSeconds) > $now;
    }

    public function isRefreshTokenValid(?int $now = null): bool
    {
        $now = $now ?? time();

        return $this->refreshTokenExpiresAt > $now;
    }

    /**
     * @return array<string, int|string>
     */
    public function toArray(): array
    {
        return [
            'accessToken' => $this->accessToken,
            'accessTokenExpiresAt' => $this->accessTokenExpiresAt,
            'refreshToken' => $this->refreshToken,
            'refreshTokenExpiresAt' => $this->refreshTokenExpiresAt,
        ];
    }

    public static function fromArray(array $data): self
    {
        $obj = new self();
        $obj->accessToken = $data['accessToken'];
        $obj->accessTokenExpiresAt = (int) $data['accessTokenExpiresAt'];
        $obj->refreshToken = $data['refreshToken'];
        $obj->refreshTokenExpiresAt = (int) $data['refreshTokenExpiresAt'];

        return $obj;
    }
}
