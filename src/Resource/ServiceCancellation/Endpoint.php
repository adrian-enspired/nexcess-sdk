<?php
/**
 * @package Nexcess-SDK
 * @license https://opensource.org/licenses/MIT
 * @copyright 2018 Nexcess.net, LLC
 */

declare(strict_types  = 1);

namespace Nexcess\Sdk\Resource\ServiceCancellation;

use Nexcess\Sdk\ {
  Resource\ServiceCancellation\ServiceCancellation,



  ApiException,
  Resource\CloudServer\Endpoint as CloudServer,
  Resource\Service,
  Resource\VirtGuestCloud\Endpoint as VirtGuestCloud,
  Resource\WritableEndpoint
};

/**
 * Represents API endpoint for client service cancellations.
 */
abstract class Endpoint extends WritableEndpoint {

  /** {@inheritDoc} */
  public const CAN_UPDATE = false;

  /** {@inheritDoc} */
  protected const _URI = 'service-cancellation';

  /** {@inheritDoc} */
  protected const _URI_CREATE = self::_URI . '/add';

  /** {@inheritDoc} */
  protected const _MODEL_FQCN = ServiceCancellation::class;
}
