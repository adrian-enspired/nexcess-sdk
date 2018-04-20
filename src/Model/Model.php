<?php
/**
 * @package Nexcess-SDK
 * @license TBD
 * @copyright 2018 Nexcess.net
 */

declare(strict_types  = 1);

namespace Nexcess\Sdk\Model;

use ArrayAccess,
  Throwable;

use Nexcess\Sdk\ {
  Client,
  Exception\ModelException,
  Util\Config
};

abstract class Model implements ArrayAccess {

  /** @var string API endpoint. */
  const ENDPOINT = '';

  /** @var string Model name. */
  const NAME = '';

  /** @var string[] Map of property aliases:names. */
  const PROPERTY_ALIASES = [];

  /** @var string[] List of property names. */
  const PROPERTY_NAMES = [];

  /** @var string[] List of readonly property names. */
  const READONLY_NAMES = [];

  /** @var array Default filter values for list(). */
  const BASE_LIST_FILTER = [];

  /** @var Config SDK client object. */
  protected $_client;

  /** @var Config SDK configuration object. */
  protected $_config;

  /** @var Response The most recently received API Response object. */
  protected $_last_response;

  /** @var array Map of last fetched property:value pairs. */
  protected $_stored = [];

  /** @var array Map of instance property:value pairs. */
  protected $_values = [];

  /**
   * @param Client $client SDK client instance.
   * @param Config $config SDK configuration object.
   */
  public function __construct(Client $client, Config $config) {
    $this->_client = $client;
    $this->_config = $config;
    $this->_sync([]);
  }

  /**
   * Fetches a paginated list of items from the API.
   *
   * @param array $filter Pagination and Model-specific filter options
   * @return array API response data
   * @throws ApiException If request fails
   */
  public function list(array $filter = []) : array {
    $response = $this->_client->request(
      'GET',
      static::ENDPOINT . "?{$this->_buildListQuery($filter)}"
    );

    $items = [];
    foreach ($response->toArray() as $data) {
      $item = new static($this->_client, $this->_config);
      $item->_sync($data);
      $items[] = $item;
    }

    return $items;
  }

  /**
   * @see https://php.net/ArrayAccess.offsetExists
   */
  public function offsetExists($name) {
    $name = static::PROPERTY_ALIASES[$name] ?? $name;
    return in_array($name, static::PROPERTY_NAMES);
  }

  /**
   * @see https://php.net/ArrayAccess.offsetGet
   */
  public function offsetGet($name) {
    if (! $this->offsetExists($name)) {
      throw new ModelException(
        ModelException::NO_SUCH_PROPERTY,
        ['name' => $name, 'model' => static::NAME]
      );
    }

    $name = static::PROPERTY_ALIASES[$name] ?? $name;

    return method_exists($this, "get{$name}") ?
      $this->{"get{$name}"}() :
      ($this->_values[$name] ?? null);
  }

  /**
   * @see https://php.net/ArrayAccess.offsetSet
   */
  public function offsetSet($name, $value) {
    $name = static::PROPERTY_ALIASES[$name] ?? $name;

    if (in_array($name, static::READONLY_NAMES)) {
      throw new ModelException(
        ModelException::READONLY_PROPERTY,
        ['name' => $name, 'model' => static::NAME]
      );
    }

    $this->_set($name, $value);
  }

  /**
   * @see https://php.net/ArrayAccess.offsetUnset
   */
  public function offsetUnset($name) {
    if (! $this->offsetExists($name)) {
      throw new ModelException(
        ModelException::NO_SUCH_PROPERTY,
        ['name' => $name, 'model' => static::NAME]
      );
    }

    $name = static::PROPERTY_ALIASES[$name] ?? $name;

    if (in_array($name, static::READONLY_NAMES)) {
      throw new ModelException(
        ModelException::READONLY_PROPERTY,
        ['name' => $name, 'model' => static::NAME]
      );
    }

    $this->_values[$name] = null;
  }

  /**
   * Fetches data from the API and syncs with this Model.
   *
   * @param int|null $id Item id
   * @return Model
   * @throws ApiException On error, or if the item doesn't exist
   */
  public function read(int $id = null) : Model {
    $id = $id ?? $this->_values['id'] ?? $this->_stored['id'] ?? null;
    if ($id === null) {
      throw new ModelException(
        ModelException::MISSING_ID,
        ['model' => static::NAME]
      );
    }

    $this->_sync($this->_client->request('GET', static::ENDPOINT . "/{$id}"));
    return $this;
  }

  /**
   * Syncs this Model with most recently fetched data from the API,
   * or re-fetches the item from the API.
   *
   * @param bool $hard Force hard sync with API?
   * @return Model
   */
  public function sync(bool $hard = false) : Model {
    if ($hard || empty($this->_stored)) {
      return $this->read();
    }

    $this->_sync($this->_stored);
    return $this;
  }

  /**
   * Builds a query string for list requests.
   *
   * @param array $filter Map of query string parameters
   * @return string A http query string
   */
  protected function _buildListQuery(array $filter) : string {
    return http_build_query($filter + static::BASE_LIST_FILTER);
  }

  /**
   * Sets a property value.
   *
   * @param string $name Property name
   * @param mixed $value Value to set
   * @throws ModelException On error
   */
  protected function _set(string $name, $value) {
    if (! $this->offsetExists($name)) {
      throw new ModelException(
        ModelException::NO_SUCH_PROPERTY,
        ['name' => $name, 'model' => static::NAME]
      );
    }

    if (method_exists($this, "set{$name}")) {
      $this->{"set{$name}"}($value);
      return;
    }

    $this->_values[$name] = $value;
  }

  /**
   * Syncs stored and local states.
   *
   * @param array $data Map of property:value pairs from API response
   */
  protected function _sync(array $data) {
    try {
      $initial = $this->_values;
      $this->_values = array_fill_keys(self::NAMES, null);

      foreach ($data as $key => $value) {
        if ($this->offsetExists($key)) {
          $this->_set($key, $value);
        }
      }

      $this->_stored = $this->_values;

    } catch (Throwable $e) {
      $this->_values = $initial;
      throw new ModelException(
        ModelException::SYNC_FAILED,
        ['model' => static::NAME, 'id' => $data['id'] ?? $initial['id'] ?? 0],
        $e
      );
    }
  }
}
