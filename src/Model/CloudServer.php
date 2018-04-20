<?php
/**
 * @package Nexcess-SDK
 * @license TBD
 * @copyright 2018 Nexcess.net
 */

declare(strict_types  = 1);

namespace Nexcess\Sdk\Model;

/**
 * Cloud Server (virtual machine).
 */
class CloudServer extends ServiceModel {

  /** {@inheritDoc} */
  const NAME = 'CloudServer';

  /** {@inheritDoc} */
  const PROPERTY_ALIASES = [
    'cloud_id' => 'location',
    'hostname' => 'host',
  ];

  /** {@inheritDoc} */
  const PROPERTY_NAMES = [
    'id',
    'cloud_id',
    'hostname',
    'package_id',
    'ssh_keys',
    'template_id'
  ];

  /** {@inheritDoc} */
  const TYPE = 'virt-guest';

  /**
   * Reboots the cloud server.
   *
   * @return array API response data
   * @throws ApiException If request fails
   */
  public function reboot() : CloudServer {
    $this->_client->request(
      'POST',
      self::ENDPOINT . "/{$cloud_server_id}",
      ['json' => ['_action' => 'reboot']]
    );

    return $this;
  }

  /**
   * Resizes the cloud server.
   *
   * @param int $package_id Desired package id
   * @return array API response data
   * @throws ApiException If request fails
   */
  public function resize(int $package_id) : CloudServer {
    $this->_client->request(
      'POST',
      self::ENDPOINT . "/{$cloud_server_id}",
      ['json' => ['_action' => 'resize', 'package_id' => $package_id]]
    );

    return $this;
  }

  /**
   * Starts (powers on) the cloud server.
   *
   * @return array API response data
   * @throws ApiException If request fails
   */
  public function start() : CloudServer {
    $this->_client->request(
      'POST',
      self::ENDPOINT . "/{$cloud_server_id}",
      ['json' => ['_action' => 'start']]
    );

    return $this;
  }

  /**
   * Stops (powers down) the cloud server.
   *
   * @return array API response data
   * @throws ApiException If request fails
   */
  public function stop() : CloudServer {
    $this->_client->request(
      'POST',
      self::ENDPOINT . "/{$cloud_server_id}",
      ['json' => ['_action' => 'stop']]
    );

    return $this;
  }

  /**
   * Views the cloud server's console log.
   *
   * @return array API response data
   * @throws ApiException If request fails
   */
  public function viewConsoleLog() : CloudServer {
    $this->_client->request(
      'GET',
      self::ENDPOINT . "/{$cloud_server_id}/console-log"
    );

    return $this;
  }
}
