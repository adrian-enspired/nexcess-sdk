<div align="center">
  <img src="https://raw.githubusercontent.com/nexcess/nexcess-php-sdk/master/.github/nexcess.png" alt="nexcess.net"/><br/>
  <img src="https://raw.githubusercontent.com/nexcess/nexcess-php-sdk/master/.github/thermo.png" alt="thermo.io"/><br/>
</div>

[_← back to Resources page_](../Resources.md)

# Cloud Accounts

Cloud-based managed hosting accounts.

**Module Name**: `CloudAccount`

**Endpoint**: `Nexcess\Sdk\Resource\CloudAccount\Endpoint`

**Entity**: `Nexcess\Sdk\Resource\CloudAccount\Entity`

-----
### Entity Properties

**Writable Properties**
- none. @see Endpoint Actions.

**Readonly Properties**
- **`app`** (Nexcess\Sdk\Resource\App\Entity): The cloud account's application environment.
- **`deploy_date`** (DateTime): The date and time the cloud account was deployed.
- **`domain`** (string): The cloud account's domain name.
- **`environment`** (array): Details about the cloud account's server environment.
- **`software`** (array): Details about software installed on the cloud account.
- **`php_version`** (string): Major.minor version of PHP installed on the cloud account.
- **`identity`** (): System-generated description.
- **`ip`** (string): Primary public IP address of the could account.
- **`is_dev_account`** (boolean): Is this a development-mode cloud account?
- **`options`** (array): Service options, such as http caching and autoscaling settings.
- **`parent_account`** (Nexcess\Sdk\Resource\CloudAccount\Entity|null): The parent cloud account, if one exists.
- **`service`** (Nexcess\Sdk\Resource\VirtGuestCloud\Entity): The service for the cloud account.
- **`location`** (Nexcess\Sdk\Resource\Cloud\Entity): The "cloud" (service location) this cloud account is hosted on.
- **`service_status`** (string): Service status (e.g., "enabled").
- **`state`** (string): Cloud account state (e.g., "stable").
- **`status`** (string): Cloud account status (e.g., "used").
- **`temp_domain`** (string): The temporary access domain for the cloud account.
- **`unix_username`** (string): The unix login name for the cloud account.

-----
### Endpoint Actions

- **Create a New Cloud Account**

Use the `create()` method to create a new Cloud Account.  **Note**, this will create a new service and charge your primary payment method on file!

Creating a cloud account will return the new Entity and account details, but remember that setting up and booting the new server can take a few minutes.

Parameters:
  - `app_id`: ID for the application environment to build on the new cloud account. @see App::list().
  - `cloud_id`: ID of the cloud (location) to create the new cloud account on. @see Cloud::list().
  - `domain`: Domain name to use for the new cloud account.
  - `install_app`: Should the application be installed? Note, this applies only to installable apps.
  - `package_id`: ID of the service package for the new cloud account. @see VirtGuestCloud::list().

```php
<?php

use Nexcess\Sdk\ {
  Resource\CloudAccount\Endpoint as ApiToken
};

// Values here are for demonstration purposes. Do not copy+paste them!

// Look up id's for the desired application enviroment, cloud location, and service package
// using their Api Endpoints, or on the client portal.

// Choose a domain name for your new cloud account.

// If you choose an installable app (e.g., WordPress or Magento)
// and wish to have it auto-installed, set `install_app` to `true`.

$newCloudAccount = $client->getEndpoint(CloudAccount::moduleName())
  ->create([
    'app_id' => 1,
    'cloud_id' => 2,
    'domain' => 'cloudy.example.com',
    'install_app' => true,
    'package_id' => 3
  ]);

$id = $newCloudAccount->getId();
// Something like 1234

$state = $newCloudAccount->get('state');
// e.g., "stable"

$domain = $newToken->get('domain');
// "cloud.example.com"

$tempDomain = $newToken->get('temp_domain');
// Something like "abcd1234.nxcli.net"
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
