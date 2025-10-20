<?php

namespace Phannp;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;
use Phannp\Exceptions\ApiException;
use Phannp\Resources\Postcards;
use Phannp\Resources\Letters;
use Phannp\Resources\Recipients;
use Phannp\Resources\Groups;
use Phannp\Resources\Events;
use Phannp\Resources\Files;
use Phannp\Resources\Reporting;
use Phannp\Resources\Campaigns;
use Phannp\Resources\Selections;
use Phannp\Resources\Account;
use Phannp\Resources\Addresses;
use Phannp\Resources\Tools;
use Phannp\Resources\SMS;

/**
 * Main client class for interacting with the Stannp API.
 * @method Postcards postcards
 * @method Letters letters
 * @method Recipients recipients
 * @method Groups groups
 * @method Events events
 * @method Files files
 * @method Reporting reporting
 * @method Campaigns campaigns
 * @method Selections selections
 * @method Account account
 * @method Addresses addresses
 * @method Tools tools
 * @method SMS sms
 *
 * @link https://www.stannp.com/us/direct-mail-api/guide
 * @package Phannp
 * @author David Varney <david.varney@gmail.com>
 * @license MIT
 */
class Client
{
    /**
     * Base URL for the Stannp API.
     * @todo Confirm this is correct for UK accounts
     * @var string BASE_URL
     */
    private const BASE_URL = 'https://api-us1.stannp.com/api/v1/';

    /**
     * API key for authenticating requests.
     * @var string $apiKey
     */
    private string $apiKey;
    /**
     * Guzzle HTTP client instance.
     * @var GuzzleClient $httpClient
     */
    private GuzzleClient $httpClient;
    /**
     * @var callable|null $dateProvider Returns a Y-m-d string for today when invoked
     */
    private $dateProvider;

    /**
     * Postcards resource.
     * @var Postcards $postcards
     */
    public Postcards $postcards;
    /**
     * Letters resource.
     * @var Letters $letters
     */
    public Letters $letters;
    /**
     * Recipients resource.
     * @var Recipients $recipients
     */
    public Recipients $recipients;
    /**
     * Groups resource.
     * @var Groups $groups
     */
    public Groups $groups;
    /**
     * Events resource.
     * @var Events $events
     */
    public Events $events;
    /**
     * Files resource.
     * @var Files $files
     */
    public Files $files;
    /**
     * Reporting resource.
     * @var Reporting $reporting
     */
    public Reporting $reporting;
    /**
     * Campaigns resource.
     * @var Campaigns $campaigns
     */
    public Campaigns $campaigns;
    /**
     * Selections resource.
     * @var Selections $selections
     */
    public Selections $selections;
    /**
     * Account resource.
     * @var Account $account
     */
    public Account $account;
    /**
     * Addresses resource.
     * @var Addresses $addresses
     */
    public Addresses $addresses;
    /**
     * Tools resource.
     * @var Tools $tools
     */
    public Tools $tools;
    /**
     * SMS resource.
     * @var SMS $sms
     */
    public SMS $sms;

    /**
     * Client constructor.
     * @param string $apiKey Your Stannp API key.
     * @param array $httpOptions Optional Guzzle HTTP client options.
     * You may also provide a 'date_provider' callable in this array for
     * testing purposes; it will be used to generate today's date in Y-m-d format.
     * @throws \InvalidArgumentException if the API key is empty
     * @todo Validate non-empty API key
     */
    public function __construct(string $apiKey, array $httpOptions = [])
    {
        $this->apiKey = $apiKey;

        // Extract a test-friendly date provider if provided. Tests can pass
        // 'date_provider' in the $httpOptions array and it will be used to
        // compute default dates (Y-m-d). The option is removed before
        // forwarding options to Guzzle.
        if (isset($httpOptions['date_provider'])) {
            $this->dateProvider = $httpOptions['date_provider'];
            unset($httpOptions['date_provider']);
        } else {
            $this->dateProvider = null;
        }

        $defaultOptions = [
            'base_uri' => self::BASE_URL,
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
            'timeout' => 30,
        ];

        $this->httpClient = new GuzzleClient(array_merge($defaultOptions, $httpOptions));

        $this->postcards = new Postcards($this);
        $this->letters = new Letters($this);
        $this->recipients = new Recipients($this);
        $this->groups = new Groups($this);
        $this->events = new Events($this);
        $this->files = new Files($this);
        $this->reporting = new Reporting($this);
        $this->campaigns = new Campaigns($this);
        $this->selections = new Selections($this);
        $this->account = new Account($this);
        $this->addresses = new Addresses($this);
        $this->tools = new Tools($this);
        $this->sms = new SMS($this);
    }

    /**
     * Return a callable that produces today's date in Y-m-d format. If a
     * date provider was registered during construction use that, otherwise
     * fall back to the system clock.
     *
     * @return callable(): string
     */
    public function getDateProvider(): callable
    {
        if (is_callable($this->dateProvider)) {
            return $this->dateProvider;
        }

        return function (): string {
            return (new \DateTimeImmutable('now'))->format('Y-m-d');
        };
    }

    /**
     * Make a GET request to the specified endpoint with optional query parameters.
     * Returns the decoded JSON response as an associative array.
     * @throws ApiException on HTTP or API errors
     */
    public function get(string $endpoint, array $params = []): array
    {
        return $this->request('GET', $endpoint, ['query' => $this->addApiKey($params)]);
    }

    /**
     * Make a POST request to the specified endpoint with optional data.
     * Returns the decoded JSON response as an associative array.
    *
    * Implementation notes:
    * - Any trailing '/' in the provided endpoint will be trimmed.
    * - If the endpoint already contains a query string, those params are preserved
    *   and merged with the client's `api_key` (the client's api_key takes precedence).
    * - The API key is sent as a query parameter (`api_key`) via Guzzle's 'query' option.
    *
    * @throws ApiException on HTTP or API errors
     */
    public function post(string $endpoint, array $data = []): array
    {
        // Normalize endpoint path and existing query
        $qPos = strpos($endpoint, '?');
        if ($qPos !== false) {
            $path = rtrim(substr($endpoint, 0, $qPos), '/');
            parse_str(substr($endpoint, $qPos + 1), $existingQuery);
        } else {
            $path = rtrim($endpoint, '/');
            $existingQuery = [];
        }

        // Ensure api_key is present in query params
        $query = array_merge($existingQuery, ['api_key' => $this->apiKey]);

        $options = $this->buildBodyOptions($data);
        $options['query'] = $query;

        return $this->request('POST', $path, $options);
    }

    /**
     * Make a PUT request to the specified endpoint with optional data.
     * Returns the decoded JSON response as an associative array.
    *
    * Implementation notes:
    * - Any trailing '/' in the provided endpoint will be trimmed.
    * - If the endpoint already contains a query string, those params are preserved
    *   and merged with the client's `api_key` (the client's api_key takes precedence).
    * - The API key is sent as a query parameter (`api_key`) via Guzzle's 'query' option.
    *
    * @throws ApiException on HTTP or API errors
     */
    public function put(string $endpoint, array $data = []): array
    {
        $qPos = strpos($endpoint, '?');
        if ($qPos !== false) {
            $path = rtrim(substr($endpoint, 0, $qPos), '/');
            parse_str(substr($endpoint, $qPos + 1), $existingQuery);
        } else {
            $path = rtrim($endpoint, '/');
            $existingQuery = [];
        }

        $query = array_merge($existingQuery, ['api_key' => $this->apiKey]);

        $options = $this->buildBodyOptions($data);
        $options['query'] = $query;

        return $this->request('PUT', $path, $options);
    }

    /**
     * Build Guzzle request body options. If any of the known file keys
     * (file, front, back) contains a resource or a local file path, use
     * multipart; otherwise use form_params.
     *
     * @param array $data
     * @return array
     */
    private function buildBodyOptions(array $data): array
    {
        $fileKeys = ['file', 'front', 'back'];

        $needsMultipart = false;
        foreach ($fileKeys as $k) {
            if (isset($data[$k])) {
                $v = $data[$k];
                if (is_resource($v) || (is_string($v) && file_exists($v))) {
                    $needsMultipart = true;
                    break;
                }
            }
        }

        if (!$needsMultipart) {
            return ['form_params' => $data];
        }

        $multipart = [];
        foreach ($data as $name => $value) {
            if (
                in_array($name, $fileKeys, true) &&
                (
                    is_resource($value) ||
                    (
                        is_string($value) &&
                        file_exists($value)
                    )
                )
            ) {
                if (is_resource($value)) {
                    $multipart[] = ['name' => $name, 'contents' => $value];
                } else {
                    $multipart[] = ['name' => $name, 'contents' => fopen($value, 'r')];
                }
            } else {
                // Scalars and arrays: send arrays as JSON strings
                if (is_array($value)) {
                    $multipart[] = ['name' => $name, 'contents' => json_encode($value)];
                } else {
                    $multipart[] = ['name' => $name, 'contents' => (string) $value];
                }
            }
        }

        return ['multipart' => $multipart];
    }

    /**
     * Make a DELETE request to the specified endpoint with optional query parameters.
     * Returns the decoded JSON response as an associative array.
    *
    * Implementation notes:
    * - The client's api_key will be merged into the provided $params and sent
    *   via Guzzle's 'query' option. This ensures the api_key is always present
    *   even when $params is an empty array.
    *
    * @throws ApiException on HTTP or API errors
     */
    public function delete(string $endpoint, array $params = []): array
    {
        // Merge api_key into the query parameters to ensure it's present even when
        // $params is empty (Guzzle's 'query' option will be used to build the final URI).
        $paramsWithKey = array_merge($params, ['api_key' => $this->apiKey]);

        // Trim trailing slash from endpoint path before passing to Guzzle
        $trimmed = rtrim($endpoint, '/');
        return $this->request('DELETE', $trimmed, ['query' => $paramsWithKey]);
    }

    // appendApiKeyToEndpoint() removed — api_key is now consistently provided via
    // the 'query' option for POST/PUT/DELETE and via addApiKey() for GET.

    /**
     * Make an HTTP request using the specified method, endpoint, and options.
     * Returns the decoded JSON response as an associative array.
     * @throws ApiException on HTTP or API errors
     * @todo Implement request throttling
     * @todo Implement request retries
     */
    private function request(string $method, string $endpoint, array $options = []): array
    {
        try {
            $response = $this->httpClient->request($method, $endpoint, $options);
            $body = (string) $response->getBody();

            return json_decode($body, true) ?? [];
        } catch (GuzzleException $e) {
            // Try to extract a response if available (RequestException)
            $response = null;
            if (method_exists($e, 'getResponse')) {
                $response = $e->getResponse();
            }

            throw \Phannp\Exceptions\ApiException::fromResponse(
                'API request failed: ' . $e->getMessage(),
                $e,
                $response
            );
        }
    }

    /**
     * Add the API key to the provided data array.
     *
     * @param array $data
     * @return array
     * @throws ApiException if the API key is not set
     */
    private function addApiKey(array $data): array
    {
        // Ensure the API key parameter is named 'api_key' per the API contract.
        return array_merge(['api_key' => $this->apiKey], $data);
    }
}
