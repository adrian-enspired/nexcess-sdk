<?php
/**
 * @package Nexcess-SDK
 * @license https://opensource.org/licenses/MIT
 * @copyright 2018 Nexcess.net, LLC
 */

declare(strict_types  = 1);

namespace Nexcess\Sdk\Resource\Ssl;

use Nexcess\Sdk\ {
  Resource\Client\Entity as Client,
  Resource\Model
};

/**
 * Orders.
 */
class Entity extends Model {

  /** {@inheritDoc} */
  public const MODULE_NAME = 'Ssl';

  /** {@inheritDoc} */
  protected const _PROPERTY_ALIASES = ['cert_id' => 'id'];

  /** {@inheritDoc} */
  protected const _PROPERTY_COLLAPSED = [
    'client' => 'client_id'
  ];

  /** {@inheritDoc} */
  protected const _PROPERTY_MODELS = [
    'client' => Client::class
  ];

  /** {@inheritDoc} */
  protected const _PROPERTY_NAMES = [
    'cert_id',
    'common_name',
    'client_id',
    'broker_id',
    'valid_from_date',
    'valid_to_date',
    'chain_crts',
    'approver_email',
    'alt_domains',
    'duns',
    'incorporating_agency',
    'identity',
    'is_real',
    'crt',
    'domain_count',
    'is_multi_domain',
    'is_wildcard',
    'is_installable',
    'is_expired',
    'alt_names',
    'id'
  ];

  /** {@inheritDoc} */
  protected const _READONLY_NAMES = [
    'cert_id',
    'common_name',
    'client_id',
    'broker_id',
    'valid_from_date',
    'valid_to_date',
    'chain_crts',
    'approver_email',
    'alt_domains',
    'duns',
    'incorporating_agency',
    'identity',
    'is_real',
    'crt',
    'domain_count',
    'is_multi_domain',
    'is_wildcard',
    'is_installable',
    'is_expired',
    'alt_names'
  ];

}
