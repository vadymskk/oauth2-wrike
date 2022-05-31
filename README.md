# Wrike Provider for OAuth 2.0 Client

This package provides Wrike OAuth 2.0 support for the PHP League's [OAuth 2.0 Client](https://github.com/thephpleague/oauth2-client).

> **_NOTE:_**  since 2.x only Wrike's v4 API is supported (prior versions support Wrike's v3 API)

## Installation

```
composer require worksection/oauth2-wrike
```

## Usage

```php
$wrikeProvider = new \Worksection\OAuth2\Client\Provider\Wrike([
    'clientId'                => 'yourId',    // The client ID assigned to you by Wrike
    'clientSecret'            => 'yourSecret',   // The client password assigned to you by the provider
    'redirectUri'             => ''
]);

// Get authorization code
if (!isset($_GET['code'])) {
    // Get authorization URL
    $authorizationUrl = $wrikeProvider->getAuthorizationUrl();

    // Get state and store it to the session
    $_SESSION['oauth2state'] = $wrikeProvider->getState();

    // Redirect user to authorization URL
    header('Location: ' . $authorizationUrl);
    exit;
// Check for errors
} elseif (empty($_GET['state']) || (isset($_SESSION['oauth2state']) && $_GET['state'] !== $_SESSION['oauth2state'])) {
    if (isset($_SESSION['oauth2state'])) {
        unset($_SESSION['oauth2state']);
    }
    exit('Invalid state');
} else {
    // Get access token
    try {
        $accessToken = $wrikeProvider->getAccessToken(
            'authorization_code',
            [
                'code' => $_GET['code']
            ]
        );
    } catch (\League\OAuth2\Client\Provider\Exception\IdentityProviderException $e) {
        exit($e->getMessage());
    }

    // Get resource owner
    try {
        $resourceOwner = $wrikeProvider->getResourceOwner($accessToken);
    } catch (\League\OAuth2\Client\Provider\Exception\IdentityProviderException $e) {
        exit($e->getMessage());
    }
        
    // Now you can store the results to session ...
    $_SESSION['accessToken'] = $accessToken;
    $_SESSION['resourceOwner'] = $resourceOwner;
        
    // ... or do some API request
    $folderId = 'yourFolderId';
    $request = $wrikeProvider->getAuthenticatedRequest(
        'GET',
        'https://www.wrike.com/api/v3/folders/' . $folderId . '/folders',
        $accessToken
    );
    try {
        $response = $wrikeProvider->getParsedResponse($request);
    } catch (\League\OAuth2\Client\Provider\Exception\IdentityProviderException $e) {
        exit($e->getMessage());
    }
    var_dump($response['data']);
}
```

For more information see the PHP League's general usage examples.

## Testing

``` bash
$ ./vendor/bin/phpunit
```

## License

The MIT License (MIT).
