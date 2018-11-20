<?php
/**
 * @package Nexcess-SDK
 * @license https://opensource.org/licenses/MIT
 * @copyright 2018 Nexcess.net, LLC
 */

declare(strict_types  = 1);

namespace Nexcess\Sdk\Resource\Ssl;

use Nexcess\Sdk\ {
  Resource\Endpoint as ReadableEndpoint,
  Resource\Ssl\Entity,
  Util\Util
};

/**
 * API endpoint for Ssl.
 */
class Endpoint extends ReadableEndpoint {

  /** {@inheritDoc} */
  public const MODULE_NAME = 'Ssl';

  /** {@inheritDoc} */
  protected const _MODEL_FQCN = Entity::class;

  /** {@inheritDoc} */
  protected const _URI = 'ssl-cert';

  /** {@inheritDoc} */
  protected const _PARAMS = [
    'retrieveByServiceId' => [
      'service_id' => [Util::TYPE_INT]
    ],
    'importCertificate' => [
      'key' => [Util::TYPE_STRING],
      'crt' => [Util::TYPE_STRING],
      'chain' => [Util::TYPE_STRING]
    ],
    'createCertificateFromCsr' => [
      'csr' => [Util::TYPE_STRING],
      'key' => [Util::TYPE_STRING],
      'months' => [Util::TYPE_INT],
      'package_id' => [Util::TYPE_INT],
      'approver_email' => [Util::TYPE_ARRAY]
    ],
    'createCertificate' => [
      'domain' => [Util::TYPE_STRING],
      'distinguished_name' => [Util::TYPE_ARRAY],
      'months' => [Util::TYPE_INT],
      'package_id' => [Util::TYPE_INT],
      'approver_email' => [Util::TYPE_ARRAY]
    ]
  ];

  /**
   * Retrieve a certificate by it's service_id
   *
   * @param int $service_id a valid service_id for a certificate
   */
  public function retrieveByServiceId(int $service_id) : Entity {
    $filter = ['filter' => ['service_id' => $service_id]];
    $response = $this->_client->request(
      'GET',
      static::_URI . "?{$this->_buildListQuery($filter)}"
    );
    return $this->getModel()->sync(Util::decodeResponse($response)[0]);
  }

  /**
   * Import an existing certificate
   *
   * @param string $key the key to the crt
   * @param string $crt the crt
   * @param string $chain The chain certificate
   *
   * @return Entity
   * @throws \GuzzleHttp\Exception\ClientException If request fails
   */
  public function importCertificate(
    string $key,
    string $crt,
    string $chain = ''
  ) : Entity {
    $response = $this->_client->post(
      self::_URI,
      ['json' => ['key' => $key, 'crt' => $crt, 'chain' => $chain]]
    );

    return $this->getModel()->sync(Util::decodeResponse($response));
  }

 /**
   * Create a new certificate from a csr
   *
   * @param string $csr A valid csr
   * @param string $key The key for the csr
   * @param int $months The number of months to make this certificate valid for.
   * @param int $package_id The SSL package purchased
   * @param array $approver_email format
   *              'domain.name' => 'approver@domain.name' Must be one of the
   *              approved 'approver emails'
   *
   * @return Entity
   * @throws \GuzzleHttp\Exception\ClientException If request fails
   */
  public function createCertificateFromCsr(
    string $csr,
    string $key,
    int $months,
    int $package_id,
    array $approver_email
  ) : Entity {
    $response = $this->_client->post(
      self::_URI,
      [
        'json' => [
          'key' => $key,
          'csr' => $csr,
          'months' => $months,
          'package_id' => $package_id,
          'approver_email' => $approver_email
        ]
      ]
    );

    return $this->getModel()->sync(Util::decodeResponse($response));
  }

/**
   * Create a new certificate
   *
   * @param string $domain the domain this certificate is for
   * @param array $distinguished_name Contains the following seven elements
   *              string email An email address used to contact the
   *                organization.
   *              string organization Legal name of the organization that owns
   *                the domain
   *              string street The street address for the owner of the domain
   *              string locality The city where the organization is located
   *              string state The state/region where the organization is
   *                located
   *              string country The two-letter code for the country where the
   *                organization is located
   * @param array $approver_email format
   *              'domain.name' => 'approver@domain.name' Must be one of the
   *              approved 'approver emails'
   *
   * @return Entity
   * @throws \GuzzleHttp\Exception\ClientException If request fails
   */
  public function createCertificate(
    string $domain,
    array $distinguished_name,
    int $months,
    int $package_id,
    array $approver_email
  ) : Entity {
    $response = $this->_client->post(
      self::_URI,
      [
        'json' => [
          'domain' => $domain,
          'months' => $months,
          'package_id' => $package_id,
          'approver_email' => $approver_email,
          'distinguished_name' => $distinguished_name
        ]
      ]
    );

    return $this->getModel()->sync(Util::decodeResponse($response));
  }

}