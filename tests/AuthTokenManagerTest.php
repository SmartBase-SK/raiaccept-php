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
    private function integrationContext(string $version = '1.2.0'): IntegrationContext
    {
        return new IntegrationContext('PLUGIN', [
            'name' => 'RaiAccept WooCommerce',
            'version' => $version,
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

    public function testAuthTokensIdentityBinding(): void
    {
        $tokens = AuthTokens::fromLoginResponse($this->loginResponse(), time());
        $context = $this->integrationContext();

        $this->assertFalse($tokens->matchesIdentity('user', $context));

        $tokens->bindIdentity('user', $context);

        $this->assertTrue($tokens->matchesIdentity('user', $context));
        $this->assertFalse($tokens->matchesIdentity('other-user', $context));
        $this->assertFalse($tokens->matchesIdentity('user', $this->integrationContext('2.0.0')));
    }

    public function testAuthTokensWithRefreshedAccessPreservesIdentity(): void
    {
        $now = 1_700_000_000;
        $stored = AuthTokens::fromLoginResponse($this->loginResponse(), $now);
        $stored->bindIdentity('user', $this->integrationContext());

        $refreshResponse = new RefreshResponse();
        $refreshResponse->accessToken = 'access-2';
        $refreshResponse->accessTokenExpiresIn = 300;

        $refreshed = $stored->withRefreshedAccess($refreshResponse, $now + 400);

        $this->assertSame('access-2', $refreshed->accessToken);
        $this->assertSame($stored->ownerKey, $refreshed->ownerKey);
        $this->assertSame($stored->contextKey, $refreshed->contextKey);
        $this->assertTrue($refreshed->matchesIdentity('user', $this->integrationContext()));
    }

    public function testAuthTokensRoundTripArray(): void
    {
        $tokens = AuthTokens::fromLoginResponse($this->loginResponse(), 1_700_000_000);
        $tokens->bindIdentity('user', $this->integrationContext());
        $restored = AuthTokens::fromArray($tokens->toArray());

        $this->assertSame($tokens->accessToken, $restored->accessToken);
        $this->assertSame($tokens->ownerKey, $restored->ownerKey);
        $this->assertSame($tokens->contextKey, $restored->contextKey);
        $this->assertTrue($restored->matchesIdentity('user', $this->integrationContext()));
    }

    public function testInMemoryTokenStorage(): void
    {
        $storage = new InMemoryTokenStorage();
        $this->assertNull($storage->get());

        $tokens = $this->boundTokens();
        $storage->save($tokens);

        $this->assertSame('access-1', $storage->get()->accessToken);

        $storage->clear();
        $this->assertNull($storage->get());
    }

    public function testManagerReturnsCachedAccessToken(): void
    {
        $storage = new InMemoryTokenStorage();
        $storage->save($this->boundTokens());

        $authClient = $this->createMock(AuthClient::class);
        $authClient->expects($this->never())->method('login');
        $authClient->expects($this->never())->method('refresh');

        $manager = new AuthTokenManager($authClient, $storage);
        $token = $manager->getAccessToken('user', 'pass', $this->integrationContext());

        $this->assertSame('access-1', $token);
    }

    public function testManagerIgnoresCachedTokenForDifferentUsername(): void
    {
        $storage = new InMemoryTokenStorage();
        $storage->save($this->boundTokens());

        $authClient = $this->createMock(AuthClient::class);
        $authClient->expects($this->never())->method('refresh');
        $authClient->expects($this->once())
            ->method('login')
            ->with('other-user', 'pass', $this->integrationContext())
            ->willReturnCallback(function ($username, $password, $context) {
                return AuthTokens::fromLoginResponse($this->loginResponse('access-other'), time())
                    ->bindIdentity($username, $context);
            });

        $manager = new AuthTokenManager($authClient, $storage);
        $token = $manager->getAccessToken('other-user', 'pass', $this->integrationContext());

        $this->assertSame('access-other', $token);
        $this->assertTrue($storage->get()->matchesIdentity('other-user', $this->integrationContext()));
    }

    public function testManagerIgnoresCachedTokenForDifferentIntegrationContext(): void
    {
        $storage = new InMemoryTokenStorage();
        $storage->save($this->boundTokens());

        $newContext = $this->integrationContext('2.0.0');
        $authClient = $this->createMock(AuthClient::class);
        $authClient->expects($this->never())->method('refresh');
        $authClient->expects($this->once())
            ->method('login')
            ->willReturnCallback(function ($username, $password, $context) {
                return AuthTokens::fromLoginResponse($this->loginResponse('access-new-context'), time())
                    ->bindIdentity($username, $context);
            });

        $manager = new AuthTokenManager($authClient, $storage);
        $token = $manager->getAccessToken('user', 'pass', $newContext);

        $this->assertSame('access-new-context', $token);
        $this->assertTrue($storage->get()->matchesIdentity('user', $newContext));
    }

    public function testManagerRefreshesExpiredAccessToken(): void
    {
        $now = time();
        $stored = $this->boundTokens($now - 400);
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
        $this->assertTrue($storage->get()->matchesIdentity('user', $this->integrationContext()));
    }

    public function testManagerLogoutRevokesTokenAndClearsStorage(): void
    {
        $storage = new InMemoryTokenStorage();
        $storage->save($this->boundTokens());

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
        $stored = $this->boundTokens($now - 400);
        $storage = new InMemoryTokenStorage();
        $storage->save($stored);

        $authClient = $this->createMock(AuthClient::class);
        $authClient->expects($this->once())
            ->method('refresh')
            ->willThrowException(new \Exception('refresh failed'));
        $authClient->expects($this->once())
            ->method('login')
            ->willReturnCallback(function ($username, $password, $context) use ($now) {
                return AuthTokens::fromLoginResponse($this->loginResponse('access-new'), $now)
                    ->bindIdentity($username, $context);
            });

        $manager = new AuthTokenManager($authClient, $storage);
        $token = $manager->getAccessToken('user', 'pass', $this->integrationContext());

        $this->assertSame('access-new', $token);
    }

    public function testManagerReturnsNullWhenAuthClientThrowsTypeError(): void
    {
        $storage = new InMemoryTokenStorage();
        $authClient = $this->createMock(AuthClient::class);
        $authClient->expects($this->once())
            ->method('login')
            ->willThrowException(new \TypeError('transport failure'));

        $manager = new AuthTokenManager($authClient, $storage);

        $this->assertNull($manager->getAccessToken('user', 'pass', $this->integrationContext()));
    }

    public function testLegacyStoredTokensWithoutIdentityTriggerLogin(): void
    {
        $storage = new InMemoryTokenStorage();
        $storage->save(AuthTokens::fromLoginResponse($this->loginResponse(), time()));

        $authClient = $this->createMock(AuthClient::class);
        $authClient->expects($this->never())->method('refresh');
        $authClient->expects($this->once())
            ->method('login')
            ->willReturnCallback(function ($username, $password, $context) {
                return AuthTokens::fromLoginResponse($this->loginResponse('access-rebound'), time())
                    ->bindIdentity($username, $context);
            });

        $manager = new AuthTokenManager($authClient, $storage);
        $token = $manager->getAccessToken('user', 'pass', $this->integrationContext());

        $this->assertSame('access-rebound', $token);
    }

    private function boundTokens(?int $now = null): AuthTokens
    {
        return AuthTokens::fromLoginResponse($this->loginResponse(), $now ?? time())
            ->bindIdentity('user', $this->integrationContext());
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
