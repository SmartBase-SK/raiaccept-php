<?php

namespace Raiaccept\RaiacceptApiClient\Model;

class LoginResponse
{
    public string $accessToken;
    public int $accessTokenExpiresIn;
    public string $refreshToken;
    public int $refreshTokenExpiresIn;

    public static function fromArray(array $data): self
    {
        $obj = new self();
        $obj->accessToken = $data['accessToken'];
        $obj->accessTokenExpiresIn = (int) $data['accessTokenExpiresIn'];
        $obj->refreshToken = $data['refreshToken'];
        $obj->refreshTokenExpiresIn = (int) $data['refreshTokenExpiresIn'];

        return $obj;
    }
}
