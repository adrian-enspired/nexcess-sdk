<?php
/**
 * @package Nexcess-SDK
 * @license https://opensource.org/licenses/MIT
 * @copyright 2018 Nexcess.net, LLC
 */

declare(strict_types  = 1);

namespace Nexcess\Sdk\Resource\Datashare;

use Nexcess\Sdk\ {
  Resource\CanDelete,
  Resource\Collection,
  Resource\Creatable,
  Resource\Datashare\Datashare,
  Resource\Deletable,
  Resource\Endpoint as BaseEndpoint,
  Resource\Modelable,
  Util\Util
};

/**
 * API actions for portal Login.
 */
class Endpoint extends BaseEndpoint implements Creatable, Deletable {
  use CanDelete;

  /** {@inheritDoc} */
  public const MODULE_NAME = 'Datashare';

  /** {@inheritDoc} */
  public const MODULE_SCOPE = 'datashare';

  /** {@inheritDoc} */
  protected const _URI = 'datashare';

  /** {@inheritDoc} */
  protected const _MODEL_FQCN = Datashare::class;

  /** {@inheritDoc} */
  protected const _PARAMS = [
    'create' => [
      'data' => [Util::TYPE_STRING],
      'owner' => [Modelable::class],
      'link_to' => [Modelable::class]
    ]
  ];

  public function create(
    string $data,
    Modelable $owner,
    Modelable $link_to
  ) : Datashare {}

  public function delete(Datashare $datashare) {}

  public function listByLinkedObject(Modelable $linked) : Collection {}

  public function listByOwner(Modelable $owner) : Collection {}

  public function retrieveByUUid(string $uuid) : Datashare {}

  public function view(Datashare $datashare) : string {}
}
