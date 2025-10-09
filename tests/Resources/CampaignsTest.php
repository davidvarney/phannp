<?php

namespace Phannp\Tests\Resources;

use Phannp\Tests\TestCase;
use GuzzleHttp\Psr7\Response;

class CampaignsTest extends TestCase
{
    public function testCrud()
    {
        $body = ['ok' => true];
        $client = $this->makeClient([
            new Response(200, [], json_encode($body)),
            new Response(200, [], json_encode($body)),
            new Response(200, [], json_encode($body)),
            new Response(200, [], json_encode($body)),
        ]);

        $this->assertSame($body, $client->campaigns->create([
            'name' => 'Test Campaign',
            'type' => 'a6-postcard',
            'template_id' => 2,
        ]));
        $this->assertSame($body, $client->campaigns->get(1));
        $this->assertSame($body, $client->campaigns->list());
        $this->assertSame($body, $client->campaigns->delete(1));
    }

    public function testProduceASample()
    {
        $body = ['ok' => true];
        [$client, $getHistory] = $this->makeClientWithHistoryPair([new \GuzzleHttp\Psr7\Response(200, [], json_encode($body))]);

        $this->assertSame($body, $client->campaigns->produceASample(123));

        $history = $getHistory();
        $this->assertCount(1, $history);

        $request = $history[0]['request'];

        $this->assertSame('GET', $request->getMethod());
        $this->assertStringContainsString('campaigns/sample', (string) $request->getUri()->getPath());

        $query = $request->getUri()->getQuery();
        $this->assertStringContainsString('id=123', $query);
        $this->assertStringContainsString('api_key=test_api_key', $query);
    }

    public function testCost()
    {
        $body = ['ok' => true];
        [$client, $getHistory] = $this->makeClientWithHistoryPair([new \GuzzleHttp\Psr7\Response(200, [], json_encode($body))]);

        $this->assertSame($body, $client->campaigns->cost(321));

        $history = $getHistory();
        $this->assertCount(1, $history);

        $request = $history[0]['request'];

        $this->assertSame('GET', $request->getMethod());
        $this->assertStringContainsString('campaigns/cost', (string) $request->getUri()->getPath());

        $query = $request->getUri()->getQuery();
        $this->assertStringContainsString('id=321', $query);
        $this->assertStringContainsString('api_key=test_api_key', $query);
    }

    public function testApprove()
    {
        $body = ['ok' => true];
        [$client, $getHistory] = $this->makeClientWithHistoryPair([new \GuzzleHttp\Psr7\Response(200, [], json_encode($body))]);

        $this->assertSame($body, $client->campaigns->approve(42));

        $history = $getHistory();
        $this->assertCount(1, $history);

        $request = $history[0]['request'];

        $this->assertSame('POST', $request->getMethod());

        // Body should be form-encoded including api_key and id
        $bodyString = (string) $request->getBody();
        $this->assertStringContainsString('id=42', $bodyString);
        $this->assertStringContainsString('api_key=test_api_key', $bodyString);

        // Content-Type should indicate form-encoded for non-multipart requests
        $this->assertTrue($request->hasHeader('Content-Type'));
        $ct = $request->getHeaderLine('Content-Type');
        $this->assertStringContainsString('application/x-www-form-urlencoded', $ct);
    }

    public function testApproveThrowsApiExceptionOn4xx()
    {
        // 400 Bad Request
        [$client] = $this->makeClientWithHistoryPair([new \GuzzleHttp\Psr7\Response(400, [], json_encode(['error' => 'bad']))]);

        try {
            $client->campaigns->approve(42);
            $this->fail('Expected ApiException was not thrown');
        } catch (\Phannp\Exceptions\ApiException $e) {
            $this->assertSame(400, $e->getStatusCode());
            $this->assertStringContainsString('error', $e->getResponseBody());
        }
    }

    public function testGetThrowsApiExceptionOn5xx()
    {
        // 500 Internal Server Error
        [$client] = $this->makeClientWithHistoryPair([new \GuzzleHttp\Psr7\Response(500, [], 'Server error')]);

        try {
            $client->campaigns->get(1);
            $this->fail('Expected ApiException was not thrown');
        } catch (\Phannp\Exceptions\ApiException $e) {
            $this->assertSame(500, $e->getStatusCode());
            $this->assertStringContainsString('Server error', $e->getResponseBody());
        }
    }

    public function testCostThrowsApiExceptionOn4xx()
    {
        [$client] = $this->makeClientWithHistoryPair([new \GuzzleHttp\Psr7\Response(400, [], json_encode(['error' => 'bad']))]);

        try {
            $client->campaigns->cost(99);
            $this->fail('Expected ApiException was not thrown');
        } catch (\Phannp\Exceptions\ApiException $e) {
            $this->assertSame(400, $e->getStatusCode());
            $this->assertStringContainsString('error', $e->getResponseBody());
        }
    }

    public function testCostThrowsApiExceptionOn5xx()
    {
        [$client] = $this->makeClientWithHistoryPair([new \GuzzleHttp\Psr7\Response(500, [], 'Server error')]);

        try {
            $client->campaigns->cost(99);
            $this->fail('Expected ApiException was not thrown');
        } catch (\Phannp\Exceptions\ApiException $e) {
            $this->assertSame(500, $e->getStatusCode());
            $this->assertStringContainsString('Server error', $e->getResponseBody());
        }
    }
}
