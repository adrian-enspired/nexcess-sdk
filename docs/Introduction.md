<div align="center">
  <img src="https://raw.githubusercontent.com/nexcess/nexcess-php-sdk/master/.github/nexcess.png" alt="nexcess.net"/><br/>
  <img src="https://raw.githubusercontent.com/nexcess/nexcess-php-sdk/master/.github/thermo.png" alt="thermo.io"/><br/>
</div>

This is the official Nexcess.net PHP SDK.

Use it for building applications which interact with the <a href="https://portal.nexcess.net/">Nexcess.net</a> and <a href="https://core.thermo.io">Thermo.io</a> Client APIs!

# Pre-Release

This is an **alpha** release.  The SDK is currently under heavy development and **will change**.

### **DO NOT USE this release in production systems.**

If you wish to use this SDK for testing, please do, but be aware that not all endpoints are supported yet, and your experience may not be bug-free.

If you find a problem or have a question, [please open an issue](https://github.com/nexcess/nexcess-php-sdk/issues).

# Table of Contents

- [Quickstart](#quickstart)
- [SDK Client]()
- [Config Objects]()
- [Resources]()
  - [Api Tokens](resources/api-tokens.md)
  - [Apps]()
  - [Clouds (datacenter locations)]()
  - [Cloud Accounts (managed hosting)](resources/cloud-accounts.md)
  - [Cloud Servers (virtual private servers)]()
  - [Invoices]()
  - [Orders]()
  - [Packages (service offerings)]()
  - [Services]()
  - [Users]()

<a name="quickstart"></a>
# Quickstart

### (0) Installation

The recommended way to download and install the Nexcess PHP SDK is [via composer](https://getcomposer.org):
```bash
composer require nexcess/nexcess-php-sdk
```

### (1) Get an Api Token

If you don't have a Nexcess or Thermo client account, sign up:
- Sign up at [Nexcess.net](https://portal.nexcess.net/sign-up)
- Sign up at [Thermo.io](https://core.thermo.io/sign-up)

Log onto the client portal and create a new API Token:
- Create a new [Nexcess Api Token](https://portal.nexcess.net/api-token)
- Create a new [Thermo Api Token](https://core.thermo.io/api-token)

### (2) Connect Using the SDK Client

The Client is the main point of entry. It is used to access API endpoints and retrieve and interact with resources. To authenticate with the API, the Client will need to be configured with the API Token created in step 1:
```php
<?php

use Nexcess\Sdk\ {
  Client,
  Util\NexcessConfig,
  Util\ThermoConfig
};

// set up autoloader. adjust path as needed.
require_once __DIR__ . '/vendor/autoload.php';

// If you're a Nexcess client, use the NexcessConfig class.
// If you're a Thermo client, use the ThermoConfig class instead.
$config = new NexcessConfig(['api_token' => 'YOUR-REALLY-LONG-API-TOKEN']);

// Use the config to create a new client:
$client = new Client($config);

// That's it!
```

> **IMPORTANT: Keep Your Api Token SECRET.**
>
> Your Api Token is like your password. Anyone who has it can log in to your client portal and do anything you can.
>
> - **NEVER commit your Api Token in source control (git, svn, etc.)!**
> - **NEVER share your Api Token with ANYONE — not even Nexcess staff!**
>
> If you think your Api Token has been used without your permission, or has accidentally been made public, log into your client portal immediately and **delete it**.

### (3) Access an Api Endpoint

Actions on the Api are performed using _Endpoint_ objects. Use the `$client` connection to access them (they'll be created and managed automatically on first use). This example shows how to access the _CloudAccount_ endpoint (for working with managed cloud hosting accounts):
```php
<?php

use Nexcess\Sdk\ {
  Resource\CloudAccount\Endpoint
};

// Get an Endpoint by its module name.
$moduleName = Endpoint::moduleName(); // "CloudAccount"
$cloudAccountEndpoint = $client->getEndpoint($moduleName);

// Using `$client->getEndpoint($moduleName)` is recommended, but the following also work:
// $endpoint = $client->getEndpoint(Endpoint::class);
// $endpoint = $client->CloudAccount;
```

### (4) Working With Api Resources

Endpoints can be used to access resources or perform other actions on the Api. For example, to _list_ Entities:
```php
<?php

// Get a Collection of cloud accounts:
$list = $cloudAccountEndpoint->list();

// Using `$endpoint->list()` is recommended, but the following also work:
// $list = $client->CloudAccount->list();
// $list = $client->CloudAccount();

// Collections are countable
echo "I have {$list->count()} Cloud Accounts.\n";

// Collections are iterable
foreach ($list as $cloudAccount) {
  echo " - Cloud Account #{$cloudAccount->getId()}: {$cloudAccount->get('domain')}\n";
  // echoes something like  - Cloud Account #1234: cloudy.example.com
}

// Collections can be filtered; filtering creates a new Collection
$php72List = $list->filter(['php_version' => '7.2']);
echo "I have {$php72List->count()} Cloud Accounts running PHP 7.2!\n";

// Filtering can also be done when retrieving the initial list:
// $php72List = $cloudAccountEndpoint->list(['php_version' => '7.2']);
```

Endpoints can _retrieve_ an individual Entity from the Api using its `id`:
```php
$id = 1234;
$cloudAccount = $cloudAccountEndpoint->retrieve($id);

// Using `$endpoint->retrieve($id)` is recommended, but the following also work:
// $item = $client->CloudAccount->retrieve($id);
// $item = $client->CloudAccount($id);
```

Entites have _properties_ which can be read (and in some cases, modified).
```php
<?php

// An Entity's id has a dedicated getter method:
$id = $cloudAccount->getId();

// Using `$item->getId()` is recommended, but the following also work:
// $id = $item->get('id');
// $id = $item['id'];

// Other properties can be accessed by their name:
$domain = $cloudAccount->get('domain');
$phpVersion = $cloudAccount->get('php_version');

// Using `$item->get($propertyName)` is recommended, but the following also works:
// $value = $item[$propertyName];
```

### Summary

```php
<?php

use Nexcess\Sdk\ {
  Client,
  Resource\CloudAccount\Endpoint,
  Util\NexcessConfig
};

require_once __DIR__ . '/vendor/autoload.php';

$config = new NexcessConfig(['api_token' => 'YOUR-REALLY-LONG-API-TOKEN']);
$client = new Client($config);

$CloudAccounts = Endpoint::moduleName();

// List cloud accounts
$list = $client->getEndpoint($CloudAccounts)->list();

// Retrieve a single cloud account by id
$cloudAccount1234 = $client->getEndpoint($CloudAccounts)->retrieve(1234);

// Get information about a cloud account
$id = $cloudAccount1234->getId();
$domain = $cloudAccount1234->get('domain');
$phpVersion = $cloudAccount1234->get('php_version');
```

### (5) Learn More

Check out the rest of the SDK documentation!
