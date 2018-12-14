<?php
/**
 * @package Nexcess-SDK
 * @license https://opensource.org/licenses/MIT
 * @copyright 2018 Nexcess.net, LLC
 */

declare(strict_types  = 1);

namespace Nexcess\Sdk\Util;

use Nexcess\Sdk\ {
  Client as SdkClient,
  Resource\ApiToken\Endpoint as ApiToken,
  Resource\App\Endpoint as App,
  Resource\Client\Endpoint as Client,
  Resource\Cloud\Endpoint as Cloud,
  Resource\CloudAccount\Endpoint as CloudAccount,
  Resource\CloudServer\Endpoint as CloudServer,
  Resource\Endpoint,
  Resource\Invoice\Endpoint as Invoice,
  Resource\Order\Endpoint as Order,
  Resource\Package\Endpoint as Package,
  Resource\Service\Endpoint as Service,
  Resource\Ssl\Endpoint as Ssl,
  Resource\User\Endpoint as User,
  Resource\VirtGuestCloud\Endpoint as VirtGuestCloud,
  SdkException
};

class EndpointRepository {

  /** @var SdkClient SDK Client instance. */
  protected $_client;

  /** @var Endpoint[] Endpoint cache. */
  protected $_endpoints = [];

  /** @var string[] Endpoint scope:fqcn map. */
  protected const _ENDPOINT_MAP = [
    ApiToken::MODULE_SCOPE => ApiToken::class,
    App::MODULE_SCOPE => App::class,
    Client::MODULE_SCOPE => Client::class,
    Cloud::MODULE_SCOPE => Cloud::class,
    CloudAccount::MODULE_SCOPE => CloudAccount::class,
    CloudServer::MODULE_SCOPE => CloudServer::class,
    Invoice::MODULE_SCOPE => Invoice::class,
    Order::MODULE_SCOPE => Order::class,
    Package::MODULE_SCOPE => Package::class,
    Service::MODULE_SCOPE => Service::class,
    Ssl::MODULE_SCOPE => Ssl::class,
    User::MODULE_SCOPE => User::class,
    VirtGuestCloud::MODULE_SCOPE => VirtGuestCloud::class
  ];

  /**
   * @param SdkClient $client SDK Client instance
   */
  public function __construct(SdkClient $client) {
    $this->_client = $client;
  }

  /**
   * Gets the API ApiToken Endpoint.
   *
   * @return ApiToken
   * @throws SdkException If endpoint is not found
   */
  public function ApiToken() : ApiToken {
    return $this->getEndpoint('ApiToken');
  }

  /**
   * Gets the API App Endpoint.
   *
   * @return App
   * @throws SdkException If endpoint is not found
   */
  public function App() : App {
    return $this->getEndpoint('App');
  }

  /**
   * Gets the API Client Endpoint.
   *
   * @return Client
   * @throws SdkException If endpoint is not found
   */
  public function Client() : Client {
    return $this->getEndpoint('Client');
  }

  /**
   * Gets the API Cloud Endpoint.
   *
   * @return Cloud
   * @throws SdkException If endpoint is not found
   */
  public function Cloud() : Cloud {
    return $this->getEndpoint('Cloud');
  }

  /**
   * Gets the API CloudAccount Endpoint.
   *
   * @return CloudAccount
   * @throws SdkException If endpoint is not found
   */
  public function CloudAccount() : CloudAccount {
    return $this->getEndpoint('CloudAccount');
  }

  /**
   * Gets the API CloudServer Endpoint.
   *
   * @return CloudServer
   * @throws SdkException If endpoint is not found
   */
  public function CloudServer() : CloudServer {
    return $this->getEndpoint('CloudServer');
  }

  /**
   * Gets an API Endpoint instance, creating it if it doesn't yet exist.
   *
   * @param string $name Endpoint module name or scope
   * @return Endpoint
   */
  public function getEndpoint(string $name) : Endpoint {
    // map module scope → module name
    $name = isset(static::_ENDPOINT_MAP[$name]) ?
      static::_ENDPOINT_MAP[$name]::moduleName() :
      $name;

    if (! isset($this->_endpoints[$name])) {
      $this->_initializeEndpoint($name);
    }

    return $this->_endpoints[$name];
  }

  /**
   * Gets the API Invoice Endpoint.
   *
   * @return Invoice
   * @throws SdkException If endpoint is not found
   */
  public function Invoice() : Invoice {
    return $this->getEndpoint('Invoice');
  }

  /**
   * Gets the API Order Endpoint.
   *
   * @return Order
   * @throws SdkException If endpoint is not found
   */
  public function Order() : Order {
    return $this->getEndpoint('Order');
  }

  /**
   * Gets the API Package Endpoint.
   *
   * @return Package
   * @throws SdkException If endpoint is not found
   */
  public function Package() : Package {
    return $this->getEndpoint('Package');
  }

  /**
   * Gets the API Service Endpoint.
   *
   * @return Service
   * @throws SdkException If endpoint is not found
   */
  public function Service() : Service {
    return $this->getEndpoint('Service');
  }

  /**
   * Gets the API Ssl Endpoint.
   *
   * @return Ssl
   * @throws SdkException If endpoint is not found
   */
  public function Ssl() : Ssl {
    return $this->getEndpoint('Ssl');
  }

  /**
   * Gets the API User Endpoint.
   *
   * @return User
   * @throws SdkException If endpoint is not found
   */
  public function User() : User {
    return $this->getEndpoint('User');
  }

  /**
   * Gets the API VirtGuestCloud Endpoint.
   *
   * @return VirtGuestCloud
   * @throws SdkException If endpoint is not found
   */
  public function VirtGuestCloud() : VirtGuestCloud {
    return $this->getEndpoint('VirtGuestCloud');
  }

  /**
   * Initializes and caches a new API Endpoint instance.
   *
   * @param string $name Endpoint module name
   * @throws SdkException If the endpoint is unknown
   */
  protected function _initializeEndpoint(string $name) : void {
    $fqcn = "\\Nexcess\\Sdk\\Resource\\{$name}\\Endpoint";
    if (! is_a($fqcn, Endpoint::class, true)) {
      throw new SdkException(
        SdkException::NO_SUCH_ENDPOINT,
        ['name' => $name]
      );
    }

    $this->_endpoints[$name] = new $fqcn($this->_client);
  }
}
