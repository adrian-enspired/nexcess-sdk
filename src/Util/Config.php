<?php
/**
 * @package Nexcess-SDK
 * @license TBD
 * @copyright 2018 Nexcess.net
 */

declare(strict_types  = 1);

namespace Nexcess\Sdk\Util;

use Nexcess\Sdk\ {
  Util\Language,
  Util\Util
};

/**
 * Config container for SDK.
 */
class Config {

  /** @var string Default language/locale. */
  const DEFAULT_LANGUAGE = Language::DEFAULT_LANGUAGE;

  /** @var array Config options. */
  private $_options = [];

  public function __construct(array $options = []) {
    $this->_options = $options;
  }

  /**
   * Allows config options to be accessed like properties.
   * @see https://php.net/__get
   */
  public function __get(string $name) {
    return $this->get($name);
  }

  /**
   * Allows config options to be accessed like properties.
   * @see https://php.net/__get
   */
  public function __set(string $name, $value) {
    return $this->set($name, $value);
  }

  /**
   * Gets a config option.
   *
   * @param string $name Name of option to get
   * @return mixed Option value on success; null otherwise
   */
  public function get(string $name) {
    return Util::dig($this->_options, $name) ?? $this->getDefault($name);
  }

  /**
   * Gets a default config option.
   *
   * @param string $name Name of option to get
   * @return mixed Option value on success; null otherwise
   */
  public function getDefault(string $name) {
    $const = 'static::DEFAULT_' . strtoupper(str_replace('.', '_', $name));
    return defined($const) ? constant($const) : null;
  }

  /**
   * Sets (overrides) a config option.
   *
   * @param string $name Name of option to set
   * @param mixed $value Value to set
   * @param bool $extend Merge array values (overwrites otherwise)?
   */
  public function set(string $name, $value, bool $extend = false) {
    if ($extend && isset($this->_options[$name])) {
      $this->_options[$name] =
        Util::extendRecursive($value, $this->_options[$name]);
      return;
    }
    $this->_options[$name] = $value;
  }
}
