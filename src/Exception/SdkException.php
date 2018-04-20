<?php
/**
 * @package Nexcess-SDK
 * @license TBD
 * @copyright 2018 Nexcess.net
 */

declare(strict_types  = 1);

namespace Nexcess\Sdk\Exception;

use Nexcess\Sdk\Exception\Exception;

class SdkException extends Exception {

  /** @var int Feature is not yet implemented. */
  const NOT_IMPLEMENTED = 1;

  /** @var int Unknown endpoint. */
  const NO_SUCH_ENDPOINT = 2;

  /** @var int Error from json_decode. */
  const JSON_DECODE_FAILURE = 3;

  /** @var int Invalid translation map or value. */
  const INVALID_LANGUAGE_MAP = 4;

  /** @var int Missing api token. */
  const MISSING_API_TOKEN = 5;

  /** @var int Unknown error. */
  const UNKNOWN_ERROR = 6;

  /** {@inheritDoc} */
  const INFO = [
    self::NOT_IMPLEMENTED => ['message' => 'not_implemented'],
    self::NO_SUCH_ENDPOINT => ['message' => 'no_such_endpoint'],
    self::JSON_DECODE_FAILURE => ['message' => 'json_decode_failure'],
    self::INVALID_LANGUAGE_MAP => ['message' => 'invalid_language_map'],
    self::MISSING_API_TOKEN => ['message' => 'missing_api_token'],
    self::UNKNOWN_ERROR => ['message' => 'unknown_error']
  ];
}
