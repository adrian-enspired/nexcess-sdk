<?php
/**
 * @package Nexcess-SDK
 * @license https://opensource.org/licenses/MIT
 * @copyright 2018 Nexcess.net, LLC
 */

declare(strict_types  = 1);

namespace Nexcess\Sdk;

use Closure,
  Throwable;

use GuzzleHttp\ {
  Client as Guzzle,
  Exception\ClientException,
  Exception\ConnectException,
  Exception\RequestException,
  Exception\ServerException,
  HandlerStack as GuzzleHandlerStack,
  MessageFormatter as GuzzleFormatter,
  Middleware as GuzzleMiddleware,
  Psr7\Request as GuzzleRequest,
  Psr7\Response as GuzzleResponse
};

use function GuzzleHttp\default_user_agent as guzzle_user_agent;

use Nexcess\Sdk\ {
  ApiException,
  Resource\Collector,
  Resource\Creatable,
  Resource\Modelable,
  Resource\Readable as Endpoint,
  Resource\Updatable,
  SdkException,
  Util\Config,
  Util\EndpointRepository,
  Util\Language,
  Util\Util
};

/**
 * API client for nexcess.net / thermo.io.
 */
class Client {

  /** @var string Api version. */
  public const API_VERSION = '0';

  /** @var string SDK root directory. */
  public const DIR = __DIR__;

  /** @var string Sdk version. */
  public const SDK_VERSION = '0.1-alpha';

  /** @var Config Client configuration object. */
  protected $_config;

  /** @var Guzzle Http client. */
  protected $_client;

  /** @var callable[] List of debug listeners. */
  protected $_debug_listeners = [];

  /** @var EndpointRepository Repository of Endpoints. */
  protected $_endpoints;

  /** @var Language Language object. */
  protected $_language;

  /** @var array API request log. */
  protected $_request_log = [];

  /**
   * @param Config $config Client configuration object
   */
  public function __construct(Config $config) {
    $this->_config = $config;
    $this->_client = $this->_newGuzzleClient();
    $this->_endpoints = $this->_newEndpointRepository();
    $this->_setLanguageHandler();
  }

  /**
   * Adds a debug listener.
   * Note, listeners will only be called in debug mode.
   *
   * The listener signature is like
   *  void $listener(string $message)
   *
   * @param callable $listener
   * @return Client $this
   */
  public function addDebugListener(callable $listener) : Client {
    $this->_debug_listeners[] = $listener;
    return $this;
  }

  /**
   * Sends a debug message to any registered listeners.
   *
   * @param string $message Debug message to send
   */
  public function debug(string $message) : void {
    foreach ($this->_debug_listeners as $listen) {
      $listen($message);
    }
  }

  /**
   * Makes a DELETE request to the Api.
   *
   * @param string $uri The URI to request
   * @param array $params Http client parameters
   * @return GuzzleResponse Api response
   * @throws ApiException On http error (4xx, 5xx, network issues, etc.)
   * @throws SdkException On any other error
   */
  public function delete(string $uri, array $params = []) : GuzzleResponse {
    return $this->request('DELETE', $uri, $params);
  }

  /**
   * Makes a GET request to the Api.
   *
   * @param string $uri The URI to request
   * @param array $params Http client parameters
   * @return GuzzleResponse Api response
   * @throws ApiException On http error (4xx, 5xx, network issues, etc.)
   * @throws SdkException On any other error
   */
  public function get(string $uri, array $params = []) : GuzzleResponse {
    return $this->request('GET', $uri, $params);
  }

  /**
   * Gets the client config object.
   *
   * @return Config
   */
  public function getConfig() : Config {
    return $this->_config;
  }

  /**
   * Gets repository for API Endpoints.
   *
   * @return EndpointRepository
   */
  public function getEndpoints() : EndpointRepository {
    return $this->_endpoints;
  }

  /**
   * Gets a log of API requests performed by this client.
   *
   * @return array[] Info about API request, categorized by endpoint
   * @throws SdkException If request logging is disabled
   */
  public function getRequestLog() : array {
    $config = $this->_config;
    if (! $config->get('request.log')) {
      throw new SdkException(SdkException::REQUEST_LOG_NOT_ENABLED);
    }

    return $this->_request_log;
  }

  /**
   * Makes a PATCH request to the Api.
   *
   * @param string $uri The URI to request
   * @param array $params Http client parameters
   * @return GuzzleResponse Api response
   * @throws ApiException On http error (4xx, 5xx, network issues, etc.)
   * @throws SdkException On any other error
   */
  public function patch(string $uri, array $params = []) : GuzzleResponse {
    return $this->request('PATCH', $uri, $params);
  }

  /**
   * Makes a POST request to the Api.
   *
   * @param string $uri The URI to request
   * @param array $params Http client parameters
   * @return GuzzleResponse Api response
   * @throws ApiException On http error (4xx, 5xx, network issues, etc.)
   * @throws SdkException On any other error
   */
  public function post(string $uri, array $params = []) : GuzzleResponse {
    return $this->request('POST', $uri, $params);
  }

  /**
   * Perform an API request.
   *
   * This is intended for use by Endpoints,
   * and should generally not be used otherwise.
   *
   * Requests and responses are logged
   * if the "debug" or "request.log" config options are true.
   *
   * @param string $method The http method to use
   * @param string $endpoint The API endpoint to request
   * @param array $params Http client parameters
   * @return GuzzleResponse Api response
   * @throws ApiException On http error (4xx, 5xx, network issues, etc.)
   * @throws SdkException On any other error
   */
  public function request(
    string $method,
    string $endpoint,
    array $params = []
  ) : GuzzleResponse {
    try {
      $params['headers'] =
        ($params['headers'] ?? []) + $this->_getDefaultHeaders();

      return $this->_client->request($method, $endpoint, $params);
    } catch (ConnectException $e) {
      throw new ApiException(ApiException::CANNOT_CONNECT, $e);
    } catch (ClientException $e) {
      switch ($e->getResponse()->getStatusCode()) {
        case 401:
          $code = ApiException::UNAUTHORIZED;
          break;
        case 403:
          $code = ApiException::FORBIDDEN;
          break;
        case 404:
          $code = ApiException::NOT_FOUND;
          break;
        case 422:
          $code = ApiException::UNPROCESSABLE_ENTITY;
          break;
        default:
          $code = ApiException::BAD_REQUEST;
          break;
      }
      throw new ApiException(
        $code,
        $e,
        ['method' => $method, 'endpoint' => $endpoint]
      );
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
      ['method' => __METHOD__]
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
      ['method' => __METHOD__]
    );
  }

  /**
   * "Streams" the request/response as they are sent/received for debugging.
   *
   * @return Closure Guzzle middleware handler
   */
  protected function _debugStreamer() : Closure {
    return function (callable $handler) {
      return function (GuzzleRequest $request, array $options) use ($handler) {
        $this->debug(
          (new GuzzleFormatter("----------\n{request}"))
            ->format($request, new GuzzleResponse())
        );
        $promised_response = $handler($request, $options);
        return $promised_response->then(
          function (GuzzleResponse $response) use ($request) {
            $this->debug(
              (new GuzzleFormatter("{response}\n----------"))
                ->format($request, $response)
            );
            return $response;
          }
        );
      };
    };
  }

  /**
   * Gets default headers for API requests.
   *
   * @return array Map of http headers
   */
  protected function _getDefaultHeaders() : array {
    $headers = [
      'Accept' => 'application/json',
      'Accept-language' => $this->_config->get('language'),
      'Api-version' => static::API_VERSION,
      'User-agent' => 'Nexcess-PHP-SDK/' . static::SDK_VERSION .
        ' (' . guzzle_user_agent() . ')'
    ];
    $api_token = $this->_config->get('api_token');
    if ($api_token) {
      $headers['Authorization'] = "Bearer {$api_token}";
    }

    return $headers;
  }

  protected function _newEndpointRepository() : EndpointRepository {
    return new EndpointRepository($this);
  }

  /**
   * Creates a new Guzzle client based on current config.
   *
   * @return Guzzle
   */
  protected function _newGuzzleClient() : Guzzle {
    $config = $this->_config;
    $defaults = $config->get('guzzle_defaults') ?? [];

    $handler = $defaults['handler'] ?? GuzzleHandlerStack::create();
    if ($config->get('debug')) {
      $handler->push($this->_debugStreamer());
    }
    if ($config->get('request.log')) {
      $handler->push(GuzzleMiddleware::history($this->_request_log));
    }

    $options = [
      'base_uri' => $config->get('base_uri'),
      'handler' => $handler
    ];

    return new Guzzle(Util::extendRecursive($options, $defaults));
  }

  /**
   * Sets up preferred language options, if configured.
   */
  protected function _setLanguageHandler() {
    $this->_language = Language::getInstance();
    $language = $this->_config->get('language');
    if (! empty($language['language'])) {
      $this->_language->setLanguage($language['language']);
    }
    if (! empty($language['paths'])) {
      $this->_language->addPaths(...$language['paths']);
    }
  }
}
