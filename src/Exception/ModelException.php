<?php
/**
 * @package Nexcess-SDK
 * @license TBD
 * @copyright 2018 Nexcess.net
 */

declare(strict_types  = 1);

namespace Nexcess\Sdk\Exception;

use Nexcess\Sdk\Exception\Exception;

class ModelException extends Exception {

  /** @var int Attempt to access a non-existant model property. */
  const NO_SUCH_PROPERTY = 1;

  /** @var int Attempt to assign a readonly property. */
  const READONLY_PROPERTY = 2;

  /** @var int Syncing model properties failed. */
  const SYNC_FAILED = 3;

  /** @var int Attempt to read from the API with no id. */
  const MISSING_ID = 4;

  /** @var int Attempt to create/update/delete a readonly API endpoint. */
  const READONLY_MODEL = 5;

  /** @var int Unknown model name. */
  const NO_SUCH_MODEL = 6;

  /** {@inheritDoc} */
  const INFO = [
    self::NO_SUCH_PROPERTY => ['message' => 'no_such_property'],
    self::READONLY_PROPERTY => ['message' => 'readonly_property'],
    self::SYNC_FAILED => ['message' => 'sync_failed'],
    self::MISSING_ID => ['message' => 'missing_id'],
    self::READONLY_MODEL => ['message' => 'readonly_model'],
    self::NO_SUCH_MODEL => ['message' => 'no_such_model']
  ];
}
