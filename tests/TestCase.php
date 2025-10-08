<?php

namespace Phannp\Tests;

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\Response;
use Phannp\Client;

class TestCase extends \PHPUnit\Framework\TestCase
{
    protected function makeClient(array $responses = [], array $options = []): Client
    {
        $mock = new MockHandler($responses ?: [new Response(200, [], json_encode(['ok' => true]))]);
        $handler = HandlerStack::create($mock);

        $guzzleOptions = array_merge(['handler' => $handler], $options);

        return new Client('test_api_key', $guzzleOptions);
    }
}
