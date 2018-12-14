<?php
/**
 * @package Nexcess-SDK
 * @license https://opensource.org/licenses/MIT
 * @copyright 2018 Nexcess.net, LLC
 */

declare(strict_types  = 1);

namespace Nexcess\Sdk\Resource;

use Nexcess\Sdk\ {
  ApiException,
  Client,
  Resource\Collection,
  Resource\Collector,
  Resource\Modelable,
  Resource\Promise,
  Resource\Readable,
  Resource\ResourceException,
  Util\Config,
  Util\Language,
  Util\Util
};

/**
 * Represents a readable API endpoint for nexcess.net / thermo.io.
 */
abstract class Endpoint implements Readable {

  /** @var string Name of resource module this endpoint belongs to. */
  public const MODULE_NAME = '';

  /** @var string Name (meta.scope) of this module's primary entity. */
  public const MODULE_SCOPE = '';

  /** @var int Key for parameter type. */
  public const PARAM_TYPE = 0;

  /** @var int Key for parameter required. */
  public const PARAM_REQUIRED = 1;

  /** @var int Key for parameter description. */
  public const PARAM_DESCRIPTION = 2;

  /** @var array Default filter values for list(). */
  protected const _BASE_LIST_FILTER = [];

  /** @var string[] Entity name:fqcn map for this endpoint. */
  protected const _ENTITY_MAP = [];

  /** @var string API endpoint. */
  protected const _URI = '';

  /** @var array[] Map of action name:parameter info pairs. */
  protected const _PARAMS = [];

  /** @var Client The Sdk Client. */
  protected $_client;

  /** @var Config Client configuration object. */
  protected $_config;

  /** @var array[] Map of last fetched property:value pairs. */
  protected $_retrieved = [];

  /**
   * {@inheritDoc}
   */
  public static function moduleName() : string {
    return static::MODULE_NAME;
  }

  /**
   * @param Client $client Api Client instance
   * @param Config $config Api Config object
   */
  public function __construct(Client $client, Config $config) {
    $this->_client = $client;
    $this->_config = $config;
  }

  /**
   * Gets an Entity belonging to this Endpoint.
   *
   * @param string $name Entity name (meta.scope)
   * @return Modelable
   * @throws SdkException If the entity is unknown
   */
  public function getEntity(string $name = null) : Modelable {
    $name = static::_ENTITY_MAP[$name] ?? $name;
    if (! is_a($name, Modelable::class, true)) {
      throw new SdkException(SdkException::NO_SUCH_MODEL, ['name' => $name]);
    }

    // belongs to this Endpoint
    $module = $name::moduleName();
    if ($module === static::MODULE_NAME) {
      return new $name($this);
    }

    // belongs to another Endpoint
    $endpoints = $this->_client->getEndpoints();
    if (method_exists($endpoints, $module)) {
      return $endpoints->{$module}()->getEntity($name);
    }

    // is an Entity but we don't know what Endpoint it belongs to
    return new $name();
  }

  /**
   * {@inheritDoc}
   */
  public function getParams(string $action) : array {
    $params = static::_PARAMS[$action] ?? [];
    foreach ($params as $param => $info) {
      if (! isset($info[self::PARAM_TYPE])) {
        $params[$param][self::PARAM_TYPE] = Util::TYPE_STRING;
      }
      if (! isset($info[self::PARAM_REQUIRED])) {
        $params[$param][self::PARAM_REQUIRED] = true;
      }
      $params[$param][self::PARAM_DESCRIPTION] =
        "{$param} ({$info[self::PARAM_TYPE]}): " .
        Language::get(
          $params[$param][self::PARAM_REQUIRED] ? 'required' : 'optional'
        ) . '. ' .
        Language::get(
          'resource.' . static::MODULE_NAME . ".{$action}.{$param}"
        );
    }

    return $params;
  }

  /**
   * {@inheritDoc}
   */
  public function list(array $filter = []) : Collector {
    $response = $this->_client->request(
      'GET',
      static::_URI . "?{$this->_buildListQuery($filter)}"
    );
    try {
      $collection = new Collection(reset(static::_ENTITY_NAMES));
      foreach (Util::decodeResponse($response) as $data) {
        $collection->add($this->getModel()->sync($data));
      }

      return $collection;
    } catch (Throwable $e) {
      throw new ApiException(
        ApiException::GOT_MALFORMED_LIST,
        ['uri' => static::_URI . "?{$this->_buildListQuery($filter)}"]
      );
    }
  }

  /**
   * {@inheritDoc}
   */
  public function retrieve(int $id) : Modelable {
    return $this->sync($this->getModel()->set('id', $id));
  }

  /**
   * {@inheritDoc}
   */
  public function sync(Modelable $model) : Modelable {
    if (! $model->isReal()) {
      throw new ResourceException(
        ResourceException::UNSYNCABLE,
        ['model' => get_class($model)]
      );
    }

    $id = $model->getId();
    $this->_retrieved[$id] = Util::decodeResponse(
      $this->_client->get(static::_URI . "/{$id}")
    );

    $model->sync($this->_retrieved[$id]);
    return $model;
  }

  /**
   * Builds a query string for list requests.
   *
   * @param array $filter Map of query string parameters
   * @return string A http query string
   */
  protected function _buildListQuery(array $filter) : string {
    $page_size = $this->_config->get('list.pageSize');
    if ($page_size) {
      $filter['pageSize'] = $filter['pageSize'] ?? $page_size;
    }

    return http_build_query($filter + static::_BASE_LIST_FILTER);
  }

  /**
   * Checks that a provided model is of the correct type for this endpoint.
   *
   * @param Modelable $model The model to check
   * @throws ApiException If the model is of the wrong class
   */
  protected function _checkModelType(Modelable $model) : void {
    if ($model->moduleName() !== $this->moduleName()) {
      throw new ApiException(
        ApiException::WRONG_MODEL_FOR_URI,
        [
          'endpoint' => static::class,
          'module' => $this->moduleName(),
          'type' => get_class($model)
        ]
      );
    }
  }

  /**
   * Gets the base URI path for this endpoint.
   *
   * @return string
   */
  protected function _getUri() : string {
    return static::_URI;
  }

  /**
   * Wraps an entity in a Promise, to resolve when a given condition is met.
   *
   * @param Modelable $resource @see Promise::__construct $resource
   * @param callable $done @see Promise::__construct $done
   * @param array $options @see Promise::__construct $options
   * @return Promise
   */
  protected function _promise(
    Modelable $resource,
    callable $done,
    array $options = []
  ) : Promise {
    $config = $this->_client->getConfig();
    $options += [
      Promise::OPT_INTERVAL => $config->get('wait.interval'),
      Promise::OPT_TICK_FN => $config->get('wait.tick_function'),
      Promise::OPT_TIMEOUT => $config->get('wait.timeout')
    ];

    return new Promise($resource, $done, $options);
  }

  /**
   * Inspects params passed for given API action and throws if invalid.
   *
   * NOTE, authoritative validation is performed by the API;
   * to prevent conflicts/confusion, validation here should remain minimal:
   * mainly limited to checking data names and types.
   *
   * @throws ResourceException If data is missing/incorrect
   */
  protected function _validateParams(string $action, array $params) : void {
    foreach ($this->getParams($action) as $param => $info) {
      if (! isset($params[$param])) {
        if ($info[self::PARAM_REQUIRED]) {
          throw new ResourceException(
            ResourceException::MISSING_PARAM,
            [
              'param' => $param,
              'module' => static::MODULE_NAME,
              'action' => $action,
              'description' => $info[self::PARAM_DESCRIPTION]
            ]
          );
        }

        continue;
      }

      if (Util::type($params[$param]) !== $info[self::PARAM_TYPE]) {
        throw new ResourceException(
          ResourceException::WRONG_PARAM,
          [
            'param' => $param,
            'module' => static::MODULE_NAME,
            'action' => $action,
            'type' => $info[self::PARAM_TYPE],
            'actual' => Util::type($params[$param]),
            'description' => $info[self::PARAM_DESCRIPTION]
          ]
        );
      }
    }
  }
}
