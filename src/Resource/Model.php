<?php
/**
 * @package Nexcess-SDK
 * @license https://opensource.org/licenses/MIT
 * @copyright 2018 Nexcess.net, LLC
 */

declare(strict_types  = 1);

namespace Nexcess\Sdk\Resource;

use DateTimeImmutable,
  DateTimeInterface as DateTime,
  Throwable;
use Nexcess\Sdk\ {
  Resource\Collection,
  Resource\Collector,
  Resource\Modelable,
  Resource\ResourceException,
  Util\Util
};

abstract class Model implements Modelable {

  /** @var string Entity name (meta.scope). */
  public const ENTITY_NAME = '';

  /** @var string Module name. */
  public const MODULE_NAME = '';

  /** @var string[] Map of property aliases:names. */
  protected const _PROPERTY_ALIASES = [];

  /** @var string[] Map of model|collection property name:id|ids name. */
  protected const _PROPERTY_COLLAPSED = [];

  /** @var string[] Map of property collection names:model classes. */
  protected const _PROPERTY_COLLECTIONS = [];

  /** @var string[] Map of property model names:model classes. */
  protected const _PROPERTY_MODELS = [];

  /** @var string[] List of read+writable property names. */
  protected const _PROPERTY_NAMES = [];

  /** @var string[] List of readonly property names. */
  protected const _READONLY_NAMES = [];

  /** @var Endpoint|null The Endpoint (if any) associated with this Model. */
  protected $_endpoint;

  /** @var bool Has this model been hydrated? */
  protected $_hydrated = false;

  /** @var array Map of instance property:value pairs. */
  protected $_values = [];

  /**
   * {@inheritDoc}
   * @see https://php.net/__set_state
   *
   * @internal
   * This method is meant for internal use only,
   * and should not be used in code.
   * Use of this method CAN result in a BROKEN object instance!
   */
  public static function __set_state($data) {
    $model = new static();
    $model->_endpoint = $data['_endpoint'] ?? null;
    $model->_values = $data['_values'] ?? [];
    return $model;
  }

  /**
   * {@inheritDoc}
   */
  public static function entityName() : string {
    return static::ENTITY_NAME;
  }

  /**
   * {@inheritDoc}
   */
  public static function moduleName() : string {
    return static::MODULE_NAME;
  }

  /**
   * Prefer using the SDK Client and Endpoints
   * (e.g., `$client->getEndpoint($module)->retrieve($id)`)
   * over instantiating models directly in your code.
   *
   * Note that models without endpoints cannot proxy api actions,
   * so most behaviors of the resource will be unavailable
   * (the resource will essentially be a value object).
   *
   * @param Endpoint|null $endpoint API Endpoint to use
   * @param int|null $id Model id
   */
  public function __construct(Endpoint $endpoint = null, int $id = null) {
    $this->sync([], true);

    if ($endpoint) {
      $this->setApiEndpoint($endpoint);
    }

    if ($id) {
      $this->set('id', $id);
    }
  }

  /**
   * {@inheritDoc}
   * @see https://php.net/__debugInfo
   */
  public function __debugInfo() {
    return [
      'module' => $this->moduleName(),
      'name' => basename(strtr(static::class, ['\\' => '/'])),
      'has_endpoint' => isset($this->_endpoint),
      'data' => $this->toArray()
    ];
  }

  /**
   * {@inheritDoc}
   */
  public function equals(Modelable $other) : bool {
    return ($other instanceof $this) &&
      $this->isReal() &&
      ($other->getId() === $this->getId());
  }

  /**
   * {@inheritDoc}
   */
  public function exists(string $name, bool $include_readonly = true) : bool {
    $name = static::_PROPERTY_ALIASES[$name] ?? $name;
    return in_array($name, static::_PROPERTY_NAMES) ||
      ($include_readonly && in_array($name, static::_READONLY_NAMES));
  }

  /**
   * {@inheritDoc}
   */
  public function get(string $name) {
    if (! $this->exists($name)) {
      $getter = str_replace('_', '', "get{$name}");
      if (method_exists($this, $getter)) {
        return $this->$getter();
      }

      throw new ResourceException(
        ResourceException::NO_SUCH_PROPERTY,
        ['name' => $name, 'model' => static::class]
      );
    }

    $name = static::_PROPERTY_ALIASES[$name] ?? $name;

    $getter = str_replace('_', '', "get{$name}");
    if (method_exists($this, $getter)) {
      return $this->$getter();
    }

    $value = Util::dig($this->_values, $name);

    // @todo revisit and decide whether this should be removed permanently
    //if (! isset($value)) {
    //  $this->_tryToHydrate();
    //  $value = Util::dig($this->_values, $name);
    //}

    return $value;
  }

  /**
   * {@inheritDoc}
   */
  public function getId() : ?int {
    return $this->_values[static::_PROPERTY_ALIASES['id'] ?? 'id'] ?? null;
  }

  /**
   * {@inheritDoc}
   */
  public function isReal() : bool {
    return $this->getId() > 0;
  }

  /**
   * {@inheritDoc}
   * @see https://php.net/JsonSerializable.jsonSerialize
   */
  public function jsonSerialize() {
    return $this->toArray(true);
  }

  /**
   * {@inheritDoc}
   * @see https://php.net/ArrayAccess.offsetExists
   */
  public function offsetExists($name) {
    return $this->exists($name);
  }

  /**
   * {@inheritDoc}
   * @see https://php.net/ArrayAccess.offsetGet
   */
  public function offsetGet($name) {
    return $this->get($name);
  }

  /**
   * {@inheritDoc}
   * @see https://php.net/ArrayAccess.offsetSet
   */
  public function offsetSet($name, $value) {
    $this->set($name, $value);
  }

  /**
   * {@inheritDoc}
   * @see https://php.net/ArrayAccess.unset
   */
  public function offsetUnset($name) {
    $this->unset($name);
  }

  /**
   * {@inheritDoc}
   */
  public function set(string $name, $value) : Modelable {
    $setter = str_replace('_', '', "set{$name}");
    if (method_exists($this, $setter)) {
      return $this->$setter();
    }

    if (! $this->exists($name, false)) {
      throw new ResourceException(
        ResourceException::NO_SUCH_WRITABLE_PROPERTY,
        ['name' => $name, 'model' => static::class]
      );
    }

    $name = static::_PROPERTY_ALIASES[$name] ?? $name;

    $setter = str_replace('_', '', "set{$name}");
    if (method_exists($this, $setter)) {
      return $this->$setter();
    }

    if (strpos($name, 'date') !== false && ! $value instanceof DateTime) {
      $unix_timestamp = Util::filter($value, Util::FILTER_INT);
      $value = new DateTimeImmutable(
        isset($unix_timestamp) ? "@{$unix_timestamp}" : $value
      );
    }

    $this->sync([$name => $value]);

    return $this;
  }

  /**
   * Attaches an Endpoint to this Model for performing API actions.
   *
   * Note, this method is intended primarily for internal use.
   * You will not need to (and should not) attach an Endpoint manually
   * when accessing models via the SDK Client (using retrieve(), etc.).
   *
   * Attaching a wrong endpoint here WILL CAUSE PROBLEMS down the line!
   *
   * @param Endpoint|null $endpoint The endpoint to attach
   * @return Model $this
   */
  public function setApiEndpoint(Endpoint $endpoint = null) : Model {
    $this->_endpoint = $endpoint;
    return $this;
  }

  /**
   * {@inheritDoc}
   */
  public function sync(array $data, bool $hard = false) : Modelable {
    $prior = $this->_values;
    try {
      if ($hard) {
        // clear state
        $this->_values = array_fill_keys(self::_PROPERTY_NAMES, null);
        $this->_hydrated = false;
      }

      foreach ($data as $key => $value) {
        $key = static::_PROPERTY_ALIASES[$key] ?? $key;
        // set property if exists (don't try to set nested properties)
        if ($this->exists($key) && strpos($key, '.') === false) {
          $setter = str_replace('_', '', "set{$key}");
          if (method_exists($this, $setter)) {
            $this->$setter($value);
            continue;
          }

          if (
            isset(static::_PROPERTY_MODELS[$key]) &&
            ! is_a($value, static::_PROPERTY_MODELS[$key])
          ) {
            $value = $this->_buildPropertyModel(
              static::_PROPERTY_MODELS[$key],
              $value
            );
          }

          if (isset(static::_PROPERTY_COLLECTIONS[$key])) {
            $value = $this->_buildPropertyCollection(
              static::_PROPERTY_COLLECTIONS[$key],
              $value
            );
          }

          if (is_int($value) && strpos($key, 'date') !== false) {
            $value = ($value === 0) ? null : $this->_buildDateTime($value);
          }

          $this->_values[$key] = $value;
        }
      }

      return $this;
    } catch (Throwable $e) {
      $this->_values = $prior;
      throw new ResourceException(
        ResourceException::SYNC_FAILED,
        $e,
        ['model' => static::class, 'id' => $data['id'] ?? $prior['id'] ?? 0]
      );
    }
  }

  /**
   * {@inheritDoc}
   */
  public function toArray(bool $recurse = true) : array {
    $properties = array_diff(
      array_merge(
        static::_PROPERTY_NAMES,
        static::_READONLY_NAMES,
        array_keys(static::_PROPERTY_ALIASES)
      ),
      static::_PROPERTY_ALIASES
    );
    usort(
      $properties,
      function ($a, $b) {
        if ($a === 'id') {
          return -1;
        }
        if ($b === 'id') {
          return 1;
        }
        return $a <=> $b;
      }
    );

    $array = [];
    foreach ($properties as $property) {
      $value = $this->get($property);
      if (is_array($value)) {
        Util::kSortRecursive($value);
      }
      if ($recurse) {
        if ($value instanceof Modelable) {
          $value = $value->toCollapsedArray() +
            ['identity' => $value->get('identity')];
        } elseif ($value instanceof Collector) {
          $value = $value->toArray(true);
        } elseif ($value instanceof DateTime) {
          $value = Util::filter($value->format('U'), Util::FILTER_INT);
        }
      }
      $array[$property] = $value;
    }
    return $array;
  }

  /**
   * {@inheritDoc}
   */
  public function toCollapsedArray(array $values = null) : array {
    $collapsed = array_fill_keys(self::_PROPERTY_NAMES, null);

    foreach ($values ?? $this->_values as $property => $value) {
      $property = static::_PROPERTY_ALIASES[$property] ?? $property;

      if ($value instanceof DateTime) {
        $value = Util::filter($value->format('U'), Util::FILTER_INT);
      }

      if (is_scalar($value) && in_array($property, static::_PROPERTY_NAMES)) {
        $collapsed[$property] = $value;
        continue;
      }

      if (! isset(static::_PROPERTY_COLLAPSED[$property])) {
        continue;
      }

      if ($value instanceof Collection) {
        $collapsed[static::_PROPERTY_COLLAPSED[$property]] = $value->getIds();
        continue;
      }

      if ($value instanceof Model) {
        $collapsed[static::_PROPERTY_COLLAPSED[$property]] = $value->getId();
        continue;
      }

      if (is_array($value)) {
        $collapsed[static::_PROPERTY_COLLAPSED[$property]] = $value['id'] ??
          array_column($value, 'id');
        continue;
      }

      throw new ResourceException(
        ResourceException::UNCOLLAPSABLE,
        ['property' => $property, 'type' => Util::type($value)]
      );
    }

    ksort($collapsed);
    return $collapsed;
  }

  /**
   * {@inheritDoc}
   */
  public function unset(string $name) : Modelable {
    if (! $this->exists($name, false)) {
      throw new ResourceException(
        ResourceException::NO_SUCH_WRITABLE_PROPERTY,
        ['name' => $name, 'model' => static::class]
      );
    }

    $name = static::_PROPERTY_ALIASES[$name] ?? $name;
    $this->_values[$name] = null;

    return $this;
  }

  /**
   * Attempts to convert given value to DateTime.
   *
   * @param int|string $datetime A unix timestamp,
   *  or any value accepted by DateTime::__construct
   * @return DateTime On success
   * @throws ResourceException On failure
   */
  protected function _buildDateTime($datetime) : DateTime {
    if ($datetime instanceof DateTime) {
      return $datetime;
    }

    if (is_int($datetime)) {
      $datetime = "@{$datetime}";
    }

    try {
      return new DateTimeImmutable($datetime);
    } catch (Throwable $e) {
      throw new ResourceException(
        ResourceException::UNDATETIMEABLE,
        ['type' => Util::type($datetime), 'datetime' => $datetime]
      );
    }
  }

  /**
   * Builds a Collection of Models given an array of ids or values.
   *
   * @param string $fqcn Fully qualified classname of model to build
   * @param int[]|array[] $values Values to build models from
   * @return Collector
   */
  protected function _buildPropertyCollection(
    string $fqcn,
    array $values
  ) : Collector {
    if ($values instanceof Collection && $values->of() === $fqcn) {
      return $values;
    }

    if (is_array($values)) {
      $collection = new Collection($fqcn);
      foreach ($values as $value) {
        $collection->add($this->_buildPropertyModel($fqcn, $value));
      }

      return $collection;
    }

    throw new ResourceException(
      ResourceException::UNCOLLECTABLE,
      ['of' => $fqcn, 'type' => Util::type($values), 'data' => $values]
    );
  }

  /**
   * Builds a Model given an id or array of values.
   *
   * @param string $fqcn Fully qualified classname of model to build
   * @param int|array $value Value(s) to build model from
   * @return Modelable
   */
  protected function _buildPropertyModel(string $fqcn, $value) : Modelable {
    if (is_int($value)) {
      $value = ['id' => $value];
    }
    if (is_array($value)) {
      return $this->_getModel($fqcn)->sync($value);
    }

    throw new ResourceException(
      ResourceException::UNMODELABLE,
      ['model' => $fqcn, 'type' => Util::type($value), 'data' => $value]
    );
  }

  /**
   * Gets an empty model instance for a child property.
   *
   * @param string $fqcn Fully qualified classname of model
   * @return Modelable
   */
  protected function _getModel(string $fqcn) : Modelable {
    return $this->_hasEndpoint() ?
      $this->_getEndpoint()->getEntity($fqcn) :
      new $fqcn();
  }

  /**
   * Gets the API Endpoint associated with this Model.
   *
   * @return Endpoint The Endpoint associated with this Model
   */
  protected function _getEndpoint() : Endpoint {
    if (! $this->_hasEndpoint()) {
      throw new ResourceException(ResourceException::NO_ENDPOINT_AVAILABLE);
    }

    return $this->_endpoint;
  }

  /**
   * Is there an API endpoint associated with this model?
   *
   * @return bool
   */
  protected function _hasEndpoint() : bool {
    return ! empty($this->_endpoint);
  }

  /**
   * Attempts to retrieve missing property values from the API.
   *
   * This method is a non-op if the model endpoint is empty,
   * if the model has already been hydrated,
   * or if the model has no id.
   */
  protected function _tryToHydrate() {
    $id = $this->getId();
    if (is_int($id) && $this->_hasEndpoint() && ! $this->_hydrated) {
      $model = $this->_getEndpoint()->retrieve($id);
      assert($model instanceof self);

      $this->_values += $model->_values;
      foreach ($this->_values as $property => $value) {
        if (isset($value)) {
          continue;
        }

        $this->_values[$property] = $model->_values[$property];
      }

      $this->_hydrated = true;
    }
  }
}
