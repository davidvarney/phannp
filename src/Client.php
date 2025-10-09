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

class Client
{
    private const BASE_URL = 'https://api-us1.stannp.com/api/v1/';

    private string $apiKey;
    private GuzzleClient $httpClient;

    public Postcards $postcards;
    public Letters $letters;
    public Recipients $recipients;
    public Groups $groups;
    public Events $events;
    public Files $files;
    public Reporting $reporting;
    public Campaigns $campaigns;
    public Selections $selections;
    public Account $account;
    public Addresses $addresses;
    public Tools $tools;
    public SMS $sms;

    public function __construct(string $apiKey, array $httpOptions = [])
    {
        $this->apiKey = $apiKey;

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

    public function get(string $endpoint, array $params = []): array
    {
        return $this->request('GET', $endpoint, ['query' => $this->addApiKey($params)]);
    }

    public function post(string $endpoint, array $data = []): array
    {
        $dataWithKey = $this->addApiKey($data);
        $options = $this->buildBodyOptions($dataWithKey);

        return $this->request('POST', $endpoint, $options);
    }

    public function put(string $endpoint, array $data = []): array
    {
        $dataWithKey = $this->addApiKey($data);
        $options = $this->buildBodyOptions($dataWithKey);

        return $this->request('PUT', $endpoint, $options);
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
            if (in_array($name, $fileKeys, true) && (is_resource($value) || (is_string($value) && file_exists($value)))) {
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

    public function delete(string $endpoint, array $params = []): array
    {
        return $this->request('DELETE', $endpoint, ['query' => $this->addApiKey($params)]);
    }

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

            throw \Phannp\Exceptions\ApiException::fromResponse('API request failed: ' . $e->getMessage(), $e, $response);
        }
    }

    private function addApiKey(array $data): array
    {
        return array_merge(['api_key' => $this->apiKey], $data);
    }
}
