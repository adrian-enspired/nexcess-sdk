-----
Title: Create a New Api Token
SDK Module: ApiToken
-----
<div align="center">
  <img src="https://raw.githubusercontent.com/nexcess/nexcess-php-sdk/master/.github/nexcess.png" alt="nexcess.net"/><br/>
  <img src="https://raw.githubusercontent.com/nexcess/nexcess-php-sdk/master/.github/thermo.png" alt="thermo.io"/><br/>
</div>

# Create a New Api Token

This guide will show you how to create a new Api Token and get its details using the PHP SDK.

1) Get the ApiToken Endpoint from the SDK Client and use its `create()` method to create the new token.

Required parameters:
- `name` (string): A name for the new token.

  The choice of name is left entirely to the user, but should be something meaningful. Good names are short, descriptive, and unique.

```php
<?php

use Nexcess\Sdk\ {
  Resource\ApiToken\Endpoint as ApiToken
};

$client = // See the Quickstart for how to create a new $client connection.

$newToken = $client->getEndpoint(ApiToken::moduleName())
  ->create(['name' => 'demo-token']);
```

2) The `create()` method returns a new ApiToken Entity. **Copy and safely store the token's value now.** You will never be able to retrieve it again.

```php
$id = $newToken->getId();
// Something like 1234
$name = $newToken->get('name');
// "demo-token"
$token = $newToken->get('token');
// Something like "afg2be7dcyooas25ojn34t98vn3y4.sue53abunchmorebase64stuff..."
```
