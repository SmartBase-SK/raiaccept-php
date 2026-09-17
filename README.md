# raiaccept-php

PHP SDK for RaiAccept payment gateway.

- **Github repository**: <https://github.com/SmartBase-SK/raiaccept-php/>

## Authentication (2.0.0+)

The SDK uses the RaiAccept Auth Service with access and refresh tokens:

- `POST /auth/api/login`
- `POST /auth/api/refresh`
- `POST /auth/api/logout`

See [CHANGELOG.md](CHANGELOG.md) for migration from 1.x Cognito authentication.

The HTTP client must implement a `send(Request $request, bool $omit_logging = false)` method. In WordPress use `WpCurlClient`.

### Get access token (recommended)

`get_access_token()` returns a Bearer token for API calls. Token caching, refresh, and re-login are handled automatically via `TokenStorage`.

```php
use Raiaccept\RaiacceptApiClient\Auth\InMemoryTokenStorage;
use Raiaccept\RaiacceptApiClient\Model\IntegrationContext;
use Raiaccept\RaiacceptApiClient\RaiAcceptService;
use Raiaccept\RaiacceptApiClient\WpCurlClient;

$client = new WpCurlClient();
$storage = new InMemoryTokenStorage();
$context = new IntegrationContext('CODE', [
    'name' => 'YourShop',
    'version' => '1.0.0',
    'vendor' => 'YourVendor',
]);

$accessToken = RaiAcceptService::get_access_token(
    $client,
    $username,
    $password,
    $context,
    $storage
);

if ($accessToken === null) {
    // invalid credentials or auth service error
}
```

Reuse the same `$storage` instance (or your own persistent storage) across requests to benefit from refresh instead of login on every call.

### PLUGIN integration context

For platform plugins (e.g. WooCommerce), use type `PLUGIN` and include environment metadata:

```php
$context = new IntegrationContext('PLUGIN', [
    'name' => 'RaiAccept WooCommerce',
    'version' => '1.2.0',
    'vendor' => 'SmartBase',
    'environmentName' => 'WooCommerce',
    'environmentVersion' => '9.4.0',
]);
```

### Call RaiAccept API

```php
use Raiaccept\RaiacceptApiClient\Api\RaiAcceptAPIApi;

$api = new RaiAcceptAPIApi($client);
$response = $api->getOrderDetails($accessToken, $orderId);
$order = $response['object'];
```

Or via service helpers:

```php
$details = RaiAcceptService::get_order_details($client, $accessToken, $orderId);
```

### Custom token storage

Implement `Raiaccept\RaiacceptApiClient\Auth\TokenStorage` to persist tokens (database, files, wp_options, etc.):

```php
use Raiaccept\RaiacceptApiClient\Auth\TokenStorage;
use Raiaccept\RaiacceptApiClient\Model\AuthTokens;

class MyTokenStorage implements TokenStorage
{
    public function get(): ?AuthTokens { /* ... */ }
    public function save(AuthTokens $tokens): void { /* ... */ }
    public function clear(): void { /* ... */ }
}
```

### Logout

Revoke a refresh token on the auth service:

```php
$success = RaiAcceptService::token_logout($client, $refreshToken);
```

Or logout using stored tokens and clear storage:

```php
use Raiaccept\RaiacceptApiClient\Auth\AuthClient;
use Raiaccept\RaiacceptApiClient\Auth\AuthTokenManager;

$manager = new AuthTokenManager(new AuthClient($client), $storage);
$manager->logout(); // calls /auth/api/logout, then clears storage
```

### Low-level auth (without token manager)

```php
use Raiaccept\RaiacceptApiClient\Auth\AuthClient;

$auth = new AuthClient($client);
$tokens = $auth->login($username, $password, $context);
// $tokens->accessToken, $tokens->refreshToken

$refreshResponse = $auth->refresh($tokens->refreshToken, $context);
// $refreshResponse->accessToken

$auth->logout($tokens->refreshToken);
```
