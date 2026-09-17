<?php

namespace Raiaccept\RaiacceptApiClient\Auth;

use Raiaccept\RaiacceptApiClient\Api\RaiAcceptAPIApi;
use Raiaccept\RaiacceptApiClient\Model\AuthTokens;
use Raiaccept\RaiacceptApiClient\Model\IntegrationContext;
use Raiaccept\RaiacceptApiClient\Model\LoginResponse;
use Raiaccept\RaiacceptApiClient\Model\RefreshResponse;
use Raiaccept\RaiacceptApiClient\Model\SanitizedInvalidArgumentException;
use Raiaccept\RaiacceptApiClient\Request;

class AuthClient
{
    public const AUTH_BASE_URL = 'https://auth.raiaccept.com';

    /** @var object */
    private $httpClient;

    private RaiAcceptAPIApi $api;

    /**
     * @param object $httpClient
     */
    public function __construct($httpClient)
    {
        $this->httpClient = $httpClient;
        $this->api = new RaiAcceptAPIApi($httpClient);
    }

    public function login(string $username, string $password, IntegrationContext $context): AuthTokens
    {
        $body = [
            'username' => $username,
            'password' => $password,
            'integrationContext' => $context->toArray(),
        ];

        $request = new Request(
            'POST',
            self::AUTH_BASE_URL . '/auth/api/login',
            ['Content-Type' => 'application/json'],
            RaiAcceptAPIApi::json_encode($body)
        );

        $result = $this->api->processRequest(
            $request,
            LoginResponse::class,
            '\Raiaccept\RaiacceptApiClient\Model\ErrorResponse',
            true
        );

        /** @var LoginResponse $response */
        $response = $result['object'];

        return AuthTokens::fromLoginResponse($response);
    }

    public function refresh(string $refreshToken, IntegrationContext $context): RefreshResponse
    {
        $body = [
            'refreshToken' => $refreshToken,
            'integrationContext' => $context->toArray(),
        ];

        $request = new Request(
            'POST',
            self::AUTH_BASE_URL . '/auth/api/refresh',
            ['Content-Type' => 'application/json'],
            RaiAcceptAPIApi::json_encode($body)
        );

        $result = $this->api->processRequest(
            $request,
            RefreshResponse::class,
            '\Raiaccept\RaiacceptApiClient\Model\ErrorResponse',
            true
        );

        /** @var RefreshResponse $response */
        $response = $result['object'];

        return $response;
    }

    public function logout(string $refreshToken): bool
    {
        if ($refreshToken === '') {
            throw new SanitizedInvalidArgumentException(
                'Missing the required parameter $refreshToken when calling logout'
            );
        }

        try {
            $request = new Request(
                'POST',
                self::AUTH_BASE_URL . '/auth/api/logout',
                ['Content-Type' => 'application/json'],
                RaiAcceptAPIApi::json_encode(['refreshToken' => $refreshToken])
            );

            $this->api->processRequest(
                $request,
                null,
                '\Raiaccept\RaiacceptApiClient\Model\ErrorResponse',
                true
            );

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
