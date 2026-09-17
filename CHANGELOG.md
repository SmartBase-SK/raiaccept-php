# Changelog

## 2.0.0-beta

Pre-release for manual QA. Stable `2.0.0` will be tagged after sandbox/production validation.

### Breaking changes

- Removed AWS Cognito authentication (`token()`, `tokenRequest()`, `getAuthRequestHeaders()`).
- Removed Cognito response models (`AuthResponse`, `AuthenticationResult`, `ChallengeParameters`).
- Replaced `RaiAcceptService::retrieve_access_token_with_credentials()` with `RaiAcceptService::get_access_token()`, which requires `IntegrationContext` and `TokenStorage`.

### Added

- RaiAccept Auth Service integration:
  - `POST https://auth.raiaccept.com/auth/api/login`
  - `POST https://auth.raiaccept.com/auth/api/refresh`
  - `POST https://auth.raiaccept.com/auth/api/logout`
- `AuthClient`, `AuthTokenManager`, `TokenStorage`, `InMemoryTokenStorage`
- `RaiAcceptService::token_logout()` and `AuthTokenManager::logout()`
- Models: `IntegrationContext`, `LoginResponse`, `RefreshResponse`, `AuthTokens`

### Migration example

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
```
