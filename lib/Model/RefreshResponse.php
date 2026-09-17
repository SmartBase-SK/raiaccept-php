<?php

namespace Raiaccept\RaiacceptApiClient\Model;

class RefreshResponse
{
    public string $accessToken;
    public int $accessTokenExpiresIn;

    public static function fromArray(array $data): self
    {
        $obj = new self();
        $obj->accessToken = $data['accessToken'];
        $obj->accessTokenExpiresIn = (int) $data['accessTokenExpiresIn'];

        return $obj;
    }
}
