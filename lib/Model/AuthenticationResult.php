<?php
/**
 * AuthenticationResult
 *
 * PHP version 7.4
 *
 * @category Class
 */

namespace Raiaccept\RaiacceptApiClient\Model;

class AuthenticationResult
{
    public string $AccessToken;
    public int $ExpiresIn;
    public string $IdToken;
    public string $RefreshToken;
    public string $TokenType;

    public static function fromArray(array $data): self
    {
        $obj = new self();
        $obj->AccessToken = $data['AccessToken'];
        $obj->ExpiresIn = $data['ExpiresIn'];
        $obj->IdToken = $data['IdToken'];
        $obj->RefreshToken = $data['RefreshToken'];
        $obj->TokenType = $data['TokenType'];
        return $obj;
    }

    public function getIdToken(): string
    {
        return $this->IdToken;
    }
}
