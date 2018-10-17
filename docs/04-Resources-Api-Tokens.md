<div align="center">
  <img src="https://raw.githubusercontent.com/nexcess/nexcess-php-sdk/master/.github/nexcess.png" alt="nexcess.net"/><br/>
  <img src="https://raw.githubusercontent.com/nexcess/nexcess-php-sdk/master/.github/thermo.png" alt="thermo.io"/><br/>
</div>

[_← back to Resources page_](04-Resources.md)

# Api Tokens

Authorization tokens for using the Nexcess/Thermo API and client web portals.

**Module Name**: `ApiToken`

**Endpoint**: `Nexcess\Sdk\Resource\ApiToken\Endpoint`

**Entity**: `Nexcess\Sdk\Resource\ApiToken\Entity`

-----
### Entity Properties

**Writable Properties**
- **`name`** (string): A user-assigned identifier for the Token.

**Readonly Properties**
- **`identity`** (string): System-generated description.
- **`token`** (string): The Token itself. Note this property will only have a value when the token is first created, and will be `null` afterwards. Be sure to write it down and store it in a safe place.

-----
### Endpoint Actions

- **Create a New Api Token**

Use the `create()` method to generate a new Api Token.

```php
<?php

use Nexcess\Sdk\ {
  Resource\ApiToken\Endpoint as ApiToken
};

// Requires a "name". Choosing a name is completely up to the user.
// Good names are unique, brief, and descriptive.
$newToken = $client->getEndpoint(ApiToken::moduleName())
  ->create(['name' => 'demo-token']);

$id = $newToken->getId();
// Something like 1234

$name = $newToken->get('name');
// "demo-token"

$token = $newToken->get('token');
// Something like "afg2be7dcyooas25ojn34t98vn3y4.sue53abunchmorebase64stuff..."
// Copy and store the token's value safely;
// you will never be able to retrieve it again.
```

- **Update an Existing Api Token**

Only a Token's `name` can be updated. To change it, assign a new name to the entity and then use the Endpoint's `update()` method.

```php
<?php

use Nexcess\Sdk\ {
  Resource\ApiToken\Endpoint as ApiToken
};

$ApiTokens = $client->getEndpoint(ApiToken::moduleName());

$demoToken = $endpoint->retrieve(1234);

// Choose and set() a new name
$demoToken->set('name', 'something-new');

// Pass the Entity to the Endpoint to be updated
$ApiTokens->update($demoToken);

// Using `$endpoint->update($entity)` is recommended, but the following also works:
// $entity->update();
```

- **Deleting an Existing Api Token**

Use the `delete()` method to delete an Api Token. The Token will immediately be removed from the system and become unusable.

```php
<?php

use Nexcess\Sdk\ {
  Resource\ApiToken\Endpoint as ApiToken
};

$ApiTokens = $client->getEndpoint(ApiToken::moduleName());

$demoToken = $endpoint->retrieve(1234);

// Pass the Entity to the Endpoint to be deleted
$ApiTokens->delete($demoToken);

// Using `$endpoint->delete($entity)` is recommended, but the following also works:
// $entity->delete();
```
