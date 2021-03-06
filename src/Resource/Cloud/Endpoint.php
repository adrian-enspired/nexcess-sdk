<?php
/**
 * @package Nexcess-SDK
 * @license https://opensource.org/licenses/MIT
 * @copyright 2018 Nexcess.net, LLC
 */

declare(strict_types  = 1);

namespace Nexcess\Sdk\Resource\Cloud;

use Nexcess\Sdk\ {
  Resource\Cloud\Cloud,
  Resource\Endpoint as ReadableEndpoint
};

/**
 * API actions for Clouds (virtual hosting clusters).
 */
class Endpoint extends ReadableEndpoint {

  /** {@inheritDoc} */
  public const MODULE_NAME = 'Cloud';

  /** {@inheritDoc} */
  protected const _URI = 'virt-cloud';

  /** {@inheritDoc} */
  protected const _MODEL_FQCN = Cloud::class;
}
