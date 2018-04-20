<?php
/**
 * @package Nexcess-SDK
 * @license TBD
 * @copyright 2018 Nexcess.net
 */

declare(strict_types  = 1);

namespace Nexcess\Sdk;

use Throwable;

use GuzzleHttp\ {
  Client as Guzzle,
  Exception\ClientException,
  Exception\ConnectException,
  Exception\RequestException,
  Exception\ServerException,
  Exception\TransferException
};

use Nexcess\Sdk\ {
  Exception\ApiException,
  Exception\ModelException,
  Exception\SdkException,
  Model\CrudModel,
  Model\Model,
  Response,
  Util\Config,
  Util\Util
};

/**
 * API client for nexcess.net / thermo.io
 */
class Client {

  /** @var string SDK root namespace. */
  const SDK_NAMESPACE = __NAMESPACE__;

  /** @var string SDK root directory. */
  const SDK_ROOT = __DIR__ . '/..';

  /** @var Config Client configuration object. */
  protected $_config;

  /** @var Guzzle Http client. */
  protected $_client;

  /**
   * @param Config $config Client configuration object
   */
  public function __construct(Config $config) {
    $this->_config = $config;

    $guzzle_options = ['base_uri' => $this->_config->get('base_uri')];
    $guzzle_defaults = $this->_config->get('guzzle_defaults');
    if ($guzzle_defaults) {
      $guzzle_options =
        Util::extendRecursive($guzzle_options, $guzzle_defaults);
    }

    $this->_client = new Guzzle($guzzle_options);
  }

  /**
   * @see https://php.net/__call
   * Allows models to be accessed and created/updated as a method call.
   *
   * @example <?php
   *  // read an existing cloud server
   *  $cloud_1 = $Client->CloudServer(1);
   *
   *  // change hostname on an existing cloud server
   *  $cloud_foo = $Client->CloudServer(['id' => 1, 'hostname' => 'foo.com']);
   *
   *  // launch a new cloud server
   *  $new_cloud = $Client->CloudServer([
   *    'cloud_id' => 1,
   *    'hostname' => 'new-cloud.com',
   *    'package_id' => 1,
   *    'template_id' => 1
   *  ]);
   *
   * @param string $name Model classname (short name or fully qualified)
   * @param mixed $args Arguments for create/read/update action
   * @return Model On success
   * @throws ModelException If Model cannot be created/read/updated/is unknown
   */
  public function __call($name, $args) {
    $model = $this->getModel($name);

    $arg = reset($args);
    if (is_array($arg)) {
      if (! $model instanceof CrudModel) {
        throw new ModelException(
          ModelException::READONLY_MODEL,
          ['model' => $name]
        );
      }

      return isset($arg['id']) ?
        $model->update($arg) :
        $model->create($arg);
    }

    return $this->getModel($method)->read($arg);
  }

  /**
   * @see https://php.net/__get
   * Allows new (empty) models to be accessed as properties.
   *
   * @example <?php  $cloud = $Client->CloudServer;
   */
  public function __get(string $name) {
    return $this->getModel($name);
  }

  /**
   * Gets a new item model.
   *
   * For convenience, models can also be accessed as Client properties.
   * @see Client::__get
   *
   * @param string $name Model classname (short name or fully qualified)
   * @return Model
   * @throws ModelException If the model is unknown
   */
  public function getModel(string $name) : Model {
    $fqcn = is_a($name, Model::class, true) ?
      $name :
      self::SDK_NAMESPACE . "\\Model\\{$name}";

    if (! is_a($fqcn, Model::class, true)) {
      throw new ModelException(
        ModelException::NO_SUCH_MODEL,
        ['name' => $name]
      );
    }

    return new $fqcn($this, $this->_config);
  }

  /**
   * Perform an API request.
   *
   * This is intended for use by Models but may be used directly.
   * Requests and responses are logged
   * if the "debug" or "request.log" config options are true.
   *
   * @param string $method The http method to use
   * @param string $endpoint The API endpoint to request
   * @param array $params Http client parameters
   * @return Response
   * @throws ApiException On http error (4xx, 5xx, network issues, etc.)
   * @throws SdkException On any other error
   */
  public function request(
    string $method,
    string $endpoint,
    array $params = []
  ) : Response {
    try {
      $params['headers'] =
        ($params['headers'] ?? []) + $this->_getDefaultHeaders();

      $request_key = null;
      if ($this->_config->get('debug') || $this->_config->get('request.log')) {
        $request_key = count($this->_request_log);
        $this->_request_log[$request_key] = [
          'method' => $method,
          'endpoint' => $endpoint,
          'params' => $params,
          'response' => null
        ];
      }

      $response = new Response(
        $this->_client()->request($method, $endpoint, $params)
      );

      if ($request_key !== null) {
        $this->_request_log[$request_key]['response'] = $response;
      }

      return $response;

    } catch (ConnectException $e) {
      throw new ApiException(ApiException::CANNOT_CONNECT, $e);
    } catch (ClientException $e) {
      throw new ApiException(ApiException::BAD_REQUEST, $e);
    } catch (ServerException $e) {
      throw new ApiException(ApiException::SERVER_ERROR, $e);
    } catch (RequestException $e) {
      throw new ApiException(ApiException::REQUEST_FAILED, $e);
    } catch (Throwable $e) {
      throw new SdkException(SdkException::UNKNOWN_ERROR, $e);
    }
  }

  /**
   * Perform self-update.
   *
   * @return array Information about the update.
   * @throws SdkException If update fails
   */
  public function selfUpdate() : array {
    throw new SdkException(
      SdkException::NOT_IMPLEMENTED,
      ['class' => __CLASS__, 'method' => __FUNCTION__]
    );
  }

  /**
   * Does the SDK need to be updated?
   *
   * @return bool True if a newer SDK version is available; false otherwise
   */
  public function shouldUpdate() : bool {
    throw new SdkException(
      SdkException::NOT_IMPLEMENTED,
      ['class' => __CLASS__, 'method' => __FUNCTION__]
    );
  }

  /**
   * Gets default headers for API requests.
   *
   * @return array Map of http headers
   */
  protected function _getDefaultHeaders() : array {
    $headers = [
      'Accept' => 'application/json',
      'Accept-language' => $this->_config->get('language')
    ];
    $api_token = $this->_config->get('api_token');
    if ($api_token) {
      $headers['Authorization'] = "Bearer {$api_token}";
    }

    return $headers;
  }
}
