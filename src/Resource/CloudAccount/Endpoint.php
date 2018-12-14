<?php
/**
 * @package Nexcess-SDK
 * @license https://opensource.org/licenses/MIT
 * @copyright 2018 Nexcess.net, LLC
 */

declare(strict_types  = 1);

namespace Nexcess\Sdk\Resource\CloudAccount;

use Throwable;

use GuzzleHttp\Cookie\CookieJar;

use Nexcess\Sdk\ {
  ApiException,
  Resource\CanCreate,
  Resource\CloudAccount\Backup,
  Resource\CloudAccount\CloudAccountException,
  Resource\CloudAccount\CloudAccount,
  Resource\Creatable,
  Resource\Collection,
  Resource\Endpoint as BaseEndpoint,
  Resource\Modelable,
  Resource\Promise,
  Util\Util
};

/**
 * API endpoint for Cloud Accounts (virtual hosting).
 */
class Endpoint extends BaseEndpoint implements Creatable {
  use CanCreate;

  /** {@inheritDoc} */
  public const MODULE_NAME = 'CloudAccount';

  /** {@inheritDoc} */
  protected const _URI = 'cloud-account';

  /** {@inheritDoc} */
  protected const _MODEL_FQCN = CloudAccount::class;

  /** {@inheritDoc} */
  protected const _PARAMS = [
    'clearNginxCache' => [],
    'create' => [
      'app_id' => [Util::TYPE_INT],
      'cloud_id' => [Util::TYPE_INT],
      'domain' => [Util::TYPE_STRING],
      'install_app' => [Util::TYPE_BOOL, false],
      'package_id' => [Util::TYPE_INT]
    ],
    'createDevAccount' => [
      'copy_account' => [Util::TYPE_BOOL],
      'domain' => [Util::TYPE_STRING],
      'package_id' => [Util::TYPE_INT],
      'ref_cloud_account_id' => [Util::TYPE_INT],
      'ref_service_id' => [Util::TYPE_INT],
      'ref_type' => [Util::TYPE_STRING],
      'scrub_account' => [Util::TYPE_BOOL]
    ],
    'setPhpVersion' => ['version' => [Util::TYPE_STRING]]
  ];

  /**
   * Requests cancellation of the service associated with a cloud account.
   *
   * Note, this creates a cancellation request,
   * and does not delete the cloud account directly.
   * Use this method to cancel a primary cloud account, not a dev account.
   *
   * @param CloudAccount $cloudaccount Cloud Server instance
   * @param array $survey Cancellation survey
   * @return CloudAccount
   */
  public function cancel(
    CloudAccount $cloudaccount,
    array $survey
  ) : CloudAccount {
    return $cloudaccount->get('service')->cancel($survey);
  }

  /**
   * Creates a development-mode CloudAccount based on given CloudAccount.
   * Note, the given CloudAccount MUST NOT be a development account itself.
   *
   * @param CloudAccount $cloudaccount CloudAccount instance
   * @return CloudAccount New dev account
   * @throws ApiException On failure
   */
  public function createDevAccount(
    CloudAccount $cloudaccount,
    array $data
  ) : CloudAccount {
    $data = [
      'domain' => ($data['domain'] ?? 'dev') .
        ".{$cloudaccount->get('domain')}",
      'ref_cloud_account_id' => $cloudaccount->getId(),
      'ref_service_id' => $cloudaccount->get('service')->getId(),
      'ref_type' => 'development'
    ] + $data
      + ['copy_account' => true, 'scrub_account' => true];
    $this->_validateParams(__FUNCTION__, $data);

    return $this->getModel()->sync(
      Util::decodeResponse(
        $this->_client->post(static::_URI, ['json' => $data])
      )
    );
  }

  /**
   * Gets php versions available for a given cloud account to use.
   *
   * @param CloudAccount $cloudaccount The subject cloud account
   * @return string[] List of available php major.minor versions
   */
  public function getAvailablePhpVersions(CloudAccount $cloudaccount) : array {
    return $cloudaccount->get('service')->getAvailablePhpVersions();
  }

  /**
   * Switches PHP versions active on an existing cloud account.
   *
   * @param CloudAccount $cloudaccount Cloud server instance
   * @param string $version Desired PHP version
   * @return CloudAccount
   * @throws ApiException If request fails
   */
  public function setPhpVersion(
    CloudAccount $cloudaccount,
    string $version
  ) : CloudAccount {
    $r = $this->_client->post(
      self::_URI . "/{$cloudaccount->getId()}",
      ['json' => ['_action' => 'set-php-version', 'php_version' => $version]]
    );

    return $cloudaccount;
  }

  /**
   * {@inheritDoc}
   * Overridden to handle both CloudAccount and secondary Entities (Backup).
   */
  public function sync(Modelable $model) : Modelable {
    if ($model instanceof Backup && $model->isReal()) {
      return $model->sync(
        $this->_findBackup($model->getCloudAccount(), $model->get('filename'))
      );
    }

    return parent::sync($model);
  }

  /**
   * Create a backup
   *
   * @param CloudAccount $cloudaccount Cloud server instance
   * @return Backup
   * @throws ApiException If request fails
   */
  public function createBackup(CloudAccount $cloudaccount) : Backup {
    $backup = $this->getModel(Backup::class);
    assert($backup instanceof Backup);

    return $backup->setCloudAccount($cloudaccount)
      ->sync(
        Util::decodeResponse(
          $this->_client->post(self::_URI . "/{$cloudaccount->getId()}/backup")
        )
      );
  }

  /**
   * Gets a new Backup entity.
   *
   * @return Backup
   */
  public function getBackupEntity() : Backup {
    return $this->getEntity(Backup::class);
  }

  /**
   * Return a list of backups
   *
   * @param CloudAccount $cloudaccount Cloud server instance
   * @return Collection Of Backups
   * @throws ApiException If request fails
   */
  public function listBackups(CloudAccount $cloudaccount) : Collection {
    $collection = new Collection(Backup::class);

    foreach ($this->_fetchBackupList($cloudaccount) as $backup_data) {
      $backup = $this->getModel(Backup::class);
      assert($backup instanceof Backup);

      $collection->add(
        $backup->setCloudAccount($cloudaccount)->sync($backup_data)
      );
    }

    return $collection;
  }

  /**
   * Return a specific backup
   *
   * @param CloudAccount $cloudaccount Cloud server instance
   * @param string $filename The unique file name for the backup to retrieve
   * @return Backup
   * @throws ApiException If request fails
   */
  public function retrieveBackup(
    CloudAccount $cloudaccount,
    string $filename
  ) : Backup {
    $backup = $this->getModel(Backup::class);
    assert($backup instanceof Backup);

    return $backup
      ->setCloudAccount($cloudaccount)
      ->sync($this->_findBackup($cloudaccount, $filename));
  }

  /**
   * Download a specific backup
   *
   * @param Backup $backup The Backup to download
   * @param string $path the directory to store the download in
   * @param bool $force download even if the file already exists?
   * @throws CloudAccountException If backup is not valid/ready for download
   * @throws Throwable
   */
  public function downloadBackup(
    Backup $backup,
    string $path,
    bool $force = false
  ) : void {

    if (! $backup->isReal()) {
      throw new CloudAccountException(
        CloudAccountException::INVALID_BACKUP,
        ['action' => __METHOD__]
      );
    }

    $download_url = $backup->get('download_url');
    if (empty($download_url) || $backup->get('complete') === false) {
      throw new CloudAccountException(
        CloudAccountException::INCOMPLETE_BACKUP,
        ['action' => __METHOD__, 'filename' => $backup->get('filename')]
      );
    }

    if (! file_exists($path) || ! is_dir($path)) {
      throw new CloudAccountException(
        CloudAccountException::INVALID_PATH,
        ['filepath' => $path]
      );
    }

    $path = trim($path);
    if (substr($path, -1) !== DIRECTORY_SEPARATOR) {
      $path .= DIRECTORY_SEPARATOR;
    }
    $save_to = $path . $backup->get('filename');

    if (file_exists($save_to)) {
      if (! $force) {
        throw new CloudAccountException(
          CloudAccountException::FILE_EXISTS,
          ['filename' => $save_to]
        );
      }

      unlink($save_to);
    }

    $stream = @fopen($save_to, 'w');
    if (! is_resource($stream)) {
      throw new CloudAccountException(
        CloudAccountException::INVALID_STREAM,
        ['filename' => $save_to]
      );
    }

    try {
      $this->_client->get(
        $download_url,
        ['cookies' => (new CookieJar()), 'sink' => $stream, 'verify' => false]
      );
    } catch (Throwable $e) {
      fclose($stream);
      unlink($save_to);
      throw $e;
    }
  }

  /**
   * Delete a specific existing backup.
   *
   * @param Backup $backup The backup to delete
   * @throws CloudAccountException If backup is invalid
   */
  public function deleteBackup(Backup $backup) : void {
    if (! $backup->isReal()) {
      throw new CloudAccountException(
        CloudAccountException::INVALID_BACKUP,
        ['action' => __METHOD__]
      );
    }

    $cloud_id = $backup->getCloudAccount()->getId();
    $filename = $backup->get('filename');
    $this->_client
      ->delete(self::_URI . "/{$cloud_id}/backup/{$filename}");
  }

  /**
   * Resolves when the given Backup is complete.
   *
   * @param Backup $backup The backup to wait for
   * @param array $options Promise options
   * @return Promise Backup[complete] = true
   */
  public function whenBackupComplete(
    Backup $backup,
    array $options = []
  ) : Promise {
    return $this->_promise(
      $backup,
      function ($backup) {
        $this->sync($backup);
        return $backup->get('complete');
      },
      $options
    );
  }

  /**
   * Clear Nginx Cache
   *
   * @param CloudAccount $cloudaccount Cloud server instance
   * @return CloudAccount
   */
  public function clearNginxCache(CloudAccount $cloudaccount) : CloudAccount {
    $this->_client->post(
      self::_URI . "/{$cloudaccount->getId()}",
      ['json' => ['_action' => 'purge-cache']]
    );

    return $cloudaccount;
  }

  /**
   * Fetch the list of backups for a given cloud account
   *
   * @param CloudAccount $cloudaccount Cloud server instance
   * @return array
   */
  protected function _fetchBackupList(CloudAccount $cloudaccount) : array {
    return Util::decodeResponse(
      $this->_client->get(self::_URI . "/{$cloudaccount->getId()}/backup")
    );
  }

  /**
   * Find data for a specific backup from the list.
   *
   * @param CloudAccount $cloudaccount The subject cloud account
   * @param string $filename The unique file name for the backup to retrieve
   * @return array
   * @throws CloudAccountException If backup not found
   */
  protected function _findBackup(
    CloudAccount $cloudaccount,
    string $filename
  ) : array {
    foreach ($this->_fetchBackupList($cloudaccount) as $backup_data) {
      if ($backup_data['filename'] === $filename) {
        return $backup_data;
      }
    }

    throw new CloudAccountException(
      CloudAccountException::BACKUP_NOT_FOUND,
      ['name' => $filename]
    );
  }
}
