<?php

namespace Raiaccept\RaiacceptApiClient\Tests;

use PHPUnit\Framework\TestCase;
use Raiaccept\RaiacceptApiClient\Auth\AuthClient;
use Raiaccept\RaiacceptApiClient\Auth\AuthTokenManager;
use Raiaccept\RaiacceptApiClient\Auth\InMemoryTokenStorage;
use Raiaccept\RaiacceptApiClient\Model\AuthTokens;
use Raiaccept\RaiacceptApiClient\Model\IntegrationContext;
use Raiaccept\RaiacceptApiClient\Model\LoginResponse;
use Raiaccept\RaiacceptApiClient\Model\RefreshResponse;

class AuthTokenManagerTest extends TestCase
{
    private function integrationContext(): IntegrationContext
    {
        return new IntegrationContext('PLUGIN', [
            'name' => 'RaiAccept WooCommerce',
            'version' => '1.2.0',
            'vendor' => 'SmartBase',
            'environmentName' => 'WooCommerce',
            'environmentVersion' => '9.0.0',
        ]);
    }

    public function testAuthTokensFromLoginResponse(): void
    {
        $now = 1_700_000_000;
        $response = new LoginResponse();
        $response->accessToken = 'access-1';
        $response->accessTokenExpiresIn = 300;
        $response->refreshToken = 'refresh-1';
        $response->refreshTokenExpiresIn = 86400;

        $tokens = AuthTokens::fromLoginResponse($response, $now);

        $this->assertSame('access-1', $tokens->accessToken);
        $this->assertSame($now + 300, $tokens->accessTokenExpiresAt);
        $this->assertSame('refresh-1', $tokens->refreshToken);
        $this->assertSame($now + 86400, $tokens->refreshTokenExpiresAt);
        $this->assertTrue($tokens->isAccessTokenValid($now, 60));
        $this->assertFalse($tokens->isAccessTokenValid($now + 250, 60));
    }

    public function testAuthTokensWithRefreshedAccess(): void
    {
        $now = 1_700_000_000;
        $stored = AuthTokens::fromLoginResponse($this->loginResponse(), $now);
        $refreshResponse = new RefreshResponse();
        $refreshResponse->accessToken = 'access-2';
        $refreshResponse->accessTokenExpiresIn = 300;

        $refreshed = $stored->withRefreshedAccess($refreshResponse, $now + 400);

        $this->assertSame('access-2', $refreshed->accessToken);
        $this->assertSame($now + 700, $refreshed->accessTokenExpiresAt);
        $this->assertSame('refresh-1', $refreshed->refreshToken);
        $this->assertSame($now + 86400, $refreshed->refreshTokenExpiresAt);
    }

    public function testAuthTokensRoundTripArray(): void
    {
        $tokens = AuthTokens::fromLoginResponse($this->loginResponse(), 1_700_000_000);
        $restored = AuthTokens::fromArray($tokens->toArray());

        $this->assertSame($tokens->accessToken, $restored->accessToken);
        $this->assertSame($tokens->accessTokenExpiresAt, $restored->accessTokenExpiresAt);
        $this->assertSame($tokens->refreshToken, $restored->refreshToken);
        $this->assertSame($tokens->refreshTokenExpiresAt, $restored->refreshTokenExpiresAt);
    }

    public function testInMemoryTokenStorage(): void
    {
        $storage = new InMemoryTokenStorage();
        $this->assertNull($storage->get());

        $tokens = AuthTokens::fromLoginResponse($this->loginResponse(), 1_700_000_000);
        $storage->save($tokens);

        $this->assertSame('access-1', $storage->get()->accessToken);

        $storage->clear();
        $this->assertNull($storage->get());
    }

    public function testManagerReturnsCachedAccessToken(): void
    {
        $storage = new InMemoryTokenStorage();
        $storage->save(AuthTokens::fromLoginResponse($this->loginResponse(), time()));

        $authClient = $this->createMock(AuthClient::class);
        $authClient->expects($this->never())->method('login');
        $authClient->expects($this->never())->method('refresh');

        $manager = new AuthTokenManager($authClient, $storage);
        $token = $manager->getAccessToken('user', 'pass', $this->integrationContext());

        $this->assertSame('access-1', $token);
    }

    public function testManagerRefreshesExpiredAccessToken(): void
    {
        $now = time();
        $stored = AuthTokens::fromLoginResponse($this->loginResponse(), $now - 400);
        $storage = new InMemoryTokenStorage();
        $storage->save($stored);

        $refreshResponse = new RefreshResponse();
        $refreshResponse->accessToken = 'access-refreshed';
        $refreshResponse->accessTokenExpiresIn = 300;

        $authClient = $this->createMock(AuthClient::class);
        $authClient->expects($this->once())
            ->method('refresh')
            ->with('refresh-1', $this->integrationContext())
            ->willReturn($refreshResponse);
        $authClient->expects($this->never())->method('login');

        $manager = new AuthTokenManager($authClient, $storage);
        $token = $manager->getAccessToken('user', 'pass', $this->integrationContext());

        $this->assertSame('access-refreshed', $token);
        $this->assertSame('access-refreshed', $storage->get()->accessToken);
    }

    public function testManagerLogoutRevokesTokenAndClearsStorage(): void
    {
        $storage = new InMemoryTokenStorage();
        $storage->save(AuthTokens::fromLoginResponse($this->loginResponse(), time()));

        $authClient = $this->createMock(AuthClient::class);
        $authClient->expects($this->once())
            ->method('logout')
            ->with('refresh-1')
            ->willReturn(true);

        $manager = new AuthTokenManager($authClient, $storage);
        $success = $manager->logout();

        $this->assertTrue($success);
        $this->assertNull($storage->get());
    }

    public function testManagerLogoutClearsStorageWhenNoTokenStored(): void
    {
        $storage = new InMemoryTokenStorage();
        $authClient = $this->createMock(AuthClient::class);
        $authClient->expects($this->never())->method('logout');

        $manager = new AuthTokenManager($authClient, $storage);

        $this->assertTrue($manager->logout());
        $this->assertNull($storage->get());
    }

    public function testManagerFallsBackToLoginWhenRefreshFails(): void
    {
        $now = time();
        $stored = AuthTokens::fromLoginResponse($this->loginResponse(), $now - 400);
        $storage = new InMemoryTokenStorage();
        $storage->save($stored);

        $authClient = $this->createMock(AuthClient::class);
        $authClient->expects($this->once())
            ->method('refresh')
            ->willThrowException(new \Exception('refresh failed'));
        $authClient->expects($this->once())
            ->method('login')
            ->willReturn(AuthTokens::fromLoginResponse($this->loginResponse('access-new'), $now));

        $manager = new AuthTokenManager($authClient, $storage);
        $token = $manager->getAccessToken('user', 'pass', $this->integrationContext());

        $this->assertSame('access-new', $token);
    }

    private function loginResponse(string $accessToken = 'access-1'): LoginResponse
    {
        $response = new LoginResponse();
        $response->accessToken = $accessToken;
        $response->accessTokenExpiresIn = 300;
        $response->refreshToken = 'refresh-1';
        $response->refreshTokenExpiresIn = 86400;

        return $response;
    }
}
