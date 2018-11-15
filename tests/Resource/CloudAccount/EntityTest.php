<?php
/**
 * @package Nexcess-SDK
 * @license https://opensource.org/licenses/MIT
 * @copyright 2018 Nexcess.net, LLC
 */

declare(strict_types  = 1);

namespace Nexcess\Sdk\Tests\Resource\CloudAccount;

use Nexcess\Sdk\ {
  Resource\CloudAccount\Endpoint,
  Resource\CloudAccount\Entity,
  Resource\CloudAccount\Backup,
  Resource\PromisedResource,
  Resource\Collection,
  Tests\Resource\ModelTestCase,
  Util\Config
};

/**
 * Unit test for cloud accounts (virtual hosting).
 */
class EntityTest extends ModelTestCase {

  /** {@inheritDoc} */
  protected const _RESOURCE_PATH = __DIR__ . '/resources';

  /** {@inheritDoc} */
  protected const _RESOURCE_FROMARRAY = 'cloud-account-1.fromArray.json';

  /** {@inheritDoc} */
  protected const _RESOURCE_TOARRAY = 'cloud-account-1.toArray-shallow.php';

  /** {@inheritDoc} */
  protected const _RESOURCE_TOCOLLAPSEDARRAY =
    'cloud-account-1.toCollapsedArray.json';

  /** {@inheritDoc} */
  protected const _SUBJECT_FQCN = Entity::class;

  /**
   * @covers Entity::getAvailablePhpVersions
   */
  public function testGetAvailablePhpVersions() {
    $versions = ['5.6', '7.0', '7.1', '7.2'];

    $endpoint = $this->createMock(Endpoint::class);
    $endpoint->expects($this->once())
      ->method('getAvailablePhpVersions')
      ->willReturn($versions);

    $entity = Entity::__set_state(['_endpoint' => $endpoint]);
    $this->assertEquals(
      $versions,
      $entity->getAvailablePhpVersions(),
      'invokes and returns $endpoint->getAvailablePhpVersions()'
    );
  }

  /**
   * @covers Entity::setPhpVersion
   */
  public function testSetPhpVersion() {
    $entity = $this->_getSubject();

    $endpoint = $this->createMock(Endpoint::class);
    $endpoint->expects($this->once())
      ->method('setPhpVersion')
      ->with($this->equalTo($entity), $this->equalTo('7.2'))
      ->willReturn($entity);

    $entity->setApiEndpoint($endpoint);

    $this->assertEquals(
      $entity,
      $entity->setPhpVersion('7.2'),
      'invokes $endpoint->setPhpversion($entity, 7.2)'
    );
  }

  /**
   * @covers Entity::backup
   */
  public function testCreateBackup() {
    $entity = $this->_getSubject();

    $backup = new Backup();
    $endpoint = $this->createMock(Endpoint::class);
    $endpoint->expects($this->once())
      ->method('createBackup')
      ->with($this->equalTo($entity))
      ->willReturn($backup);

    $entity->setApiEndpoint($endpoint);

    $this->assertEquals(
      $backup,
      $entity->backup(),
      'invokes $endpoint->createBackup($entity)'
    );
  }

  /**
   * @covers Entity::listBackups
   */
  public function testListBackups() {
    $entity = $this->_getSubject();

    $collection = new Collection(Backup::class);
    $endpoint = $this->createMock(Endpoint::class);
    $endpoint->expects($this->once())
      ->method('listBackups')
      ->with($this->equalTo($entity))
      ->willReturn($collection);

    $entity->setApiEndpoint($endpoint);

    $this->assertEquals(
      $collection,
      $entity->listBackups(),
      'invokes $endpoint->listBackups($entity)'
    );
  }

  /**
   * @covers Entity::retrieveBackup
   */
  public function testRetrieveBackup() {
    $entity = $this->_getSubject();
    $filename = 'filename.tgz';

    $backup = $this->createMock(Backup::class);
    $backup->expects($this->once())
      ->method('get')
      ->with('filename')
      ->willReturn($filename);

    $endpoint = $this->createMock(Endpoint::class);
    $endpoint->expects($this->once())
      ->method('retrieveBackup')
      ->with($this->equalTo($entity), $this->equalTo($filename))
      ->willReturn($backup);

    $entity->setApiEndpoint($endpoint);

    $this->assertEquals(
      $filename,
      $entity->retrieveBackup($filename)->get('filename'),
      'invokes $endpoint->retrieveBackup($entity)'
    );
  }

}
