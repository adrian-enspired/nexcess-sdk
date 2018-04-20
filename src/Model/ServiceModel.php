<?php
/**
 * @package Nexcess-SDK
 * @license TBD
 * @copyright 2018 Nexcess.net
 */

declare(strict_types  = 1);

namespace Nexcess\Sdk\Model;

use Nexcess\Sdk\ {
  Exception\ModelException,
  Model\CrudModel
};

abstract class ServiceModel extends CrudModel {

  /** {@inheritDoc} */
  const ENDPOINT = 'service';

  /** @var string Service type. */
  const TYPE = '';

  /**
   * Requests a service cancellation.
   *
   * @param int $id Service id to cancel
   * @param array $survey Cancellation survey
   * @return array API response data
   * @throws ApiException If request fails
   */
  public function cancel(int $id, array $survey) : Response {
    throw new SdkException(SdkException::NOT_IMPLEMENTED);
  }

  /**
   * Overridden to set service type on list queries.
   */
  protected function _buildListQuery(array $filter) : string {
    return parent::_buildListQuery(['type' => static::TYPE] + $filter);
  }
}
