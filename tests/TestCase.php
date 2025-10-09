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

    /**
     * Create a Client with a Guzzle history middleware so tests may inspect
     * the outgoing requests. Returns an array with the client and a reference
     * to the history array: [Client, array & $history]
     *
     * @param array $responses
     * @param array $options
     * @return array [Client, array&]
     */
    protected function makeClientWithHistory(array $responses = [], array $options = [], array & $history = []): Client
    {
        $mock = new MockHandler($responses ?: [new Response(200, [], json_encode(['ok' => true]))]);
        $history = [];
        $historyMiddleware = \GuzzleHttp\Middleware::history($history);

        $handler = HandlerStack::create($mock);
        $handler->push($historyMiddleware);

        $guzzleOptions = array_merge(['handler' => $handler], $options);

        return new Client('test_api_key', $guzzleOptions);
    }

    /**
     * Ergonomic wrapper that returns [Client, History] so tests don't need to
     * pre-declare a history variable.
     *
     * @param array $responses
     * @param array $options
     * @return array [Client, array]
     */
    protected function makeClientWithHistoryPair(array $responses = [], array $options = []): array
    {
        $history = [];
        $client = $this->makeClientWithHistory($responses, $options, $history);

        // Return a getter closure so the caller can obtain the up-to-date history
        $getHistory = function () use (&$history) {
            return $history;
        };

        return [$client, $getHistory];
    }
}
