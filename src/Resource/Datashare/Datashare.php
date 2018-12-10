<?php
/**
 * @package Nexcess-SDK
 * @license https://opensource.org/licenses/MIT
 * @copyright 2018 Nexcess.net, LLC
 */

declare(strict_types  = 1);

namespace Nexcess\Sdk\Resource\ApiToken;

use Nexcess\Sdk\ {
  Resource\Model,
  Resource\Modelable
};

/**
 * API Token.
 */
class Datashare extends Model {

  /** {@inheritDoc} */
  public const MODULE_NAME = 'Datashare';

  /** {@inheritDoc} */
  public const MODULE_SCOPE = 'datashare';

  /** {@inheritDoc} */
  protected const _PROPERTY_ALIASES = ['id' => 'share_id'];

  /** {@inheritDoc} */
  protected const _PROPERTY_NAMES = [
    'link_to',
    'link_to_id',
    'owner',
    'owner_id',
    'share_id'
  ];

  /** {@inheritDoc} */
  protected const _READONLY_NAMES = [
    'expiration_date',
    'expired',
    'identity',
    'max_uses',
    'share_date',
    'type',
    'uses'
  ];

  public function getLinkedObject() : Modelable {
    if (empty($this->_linked_object)) {
      $this->_buildLinkedObject();
    }

    return $this->_linked_object;
  }

  public function getOwner() : Modelable {
    if (empty($this->_owner_object)) {
      $this->_buildOwnerObject();
    }

    return $this->_owner_object;
  }

  public function setLinkedObject(Modelable $linked) : Datashare {
    $this->_values['link_to'] = $linked->getModuleScope();
    $this->_values['link_to_id'] = $linked->getId();
    $this->_linked_object = $linked;
  }

  public function setOwner(Modelable $owner) : Datashare {
    $this->_values['owner'] = $owner->getModuleScope();
    $this->_values['owner_id'] = $owner->getId();
    $this->_linked_object = $owner;
  }

  protected function _buildLinkedObject() {
    //
  }
}
