<?php
/**
 * AuthResponse
 *
 * PHP version 7.4
 *
 * @category Class
 */

namespace Raiaccept\RaiacceptApiClient\Model;

class AuthResponse
{
    public AuthenticationResult $authenticationResult;
    public ChallengeParameters $challengeParameters;

    public static function fromArray(array $data): self
    {
        $obj = new self();
        $obj->authenticationResult = AuthenticationResult::fromArray($data['AuthenticationResult']);
        $obj->challengeParameters = ChallengeParameters::fromArray($data['ChallengeParameters']);
        return $obj;
    }

    public function getIdToken(): string
    {
        if ( is_null($this->authenticationResult) ) {
            return "";
        }
        return $this->authenticationResult->getIdToken();
    }
}
