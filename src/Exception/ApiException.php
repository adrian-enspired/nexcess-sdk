<?php
/**
 * @package Nexcess-SDK
 * @license TBD
 * @copyright 2018 Nexcess.net
 */

declare(strict_types  = 1);

namespace Nexcess\Sdk\Exception;

use Nexcess\Sdk\Exception\Exception;

use Nexcess\Sdk\Util\I18n;

class ApiException extends Exception {

  /** @var int Networking/connectivity issue. */
  const CANNOT_CONNECT = 1;

  /** @var int Bad request (i.e., 4xx response). */
  const BAD_REQUEST = 2;

  /** @var int Server error (i.e., 5xx response). */
  const SERVER_ERROR = 3;

  /** @var int Other guzzle problem. */
  const REQUEST_FAILED = 4;

  /** {@inheritDoc} */
  const INFO = [
    self::CANNOT_CONNECT => ['message' => 'cannot_connect'],
    self::BAD_REQUEST => ['message' => 'bad_request'],
    self::SERVER_ERROR => ['message' => 'server_error'],
    self::REQUEST_FAILED => ['message' => 'request_failed']
  ];
}
