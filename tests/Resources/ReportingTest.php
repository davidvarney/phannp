<?php

namespace Phannp\Tests\Resources;

use Phannp\Tests\TestCase;
use GuzzleHttp\Psr7\Response;

class ReportingTest extends TestCase
{
    public function testGetStatsAndCampaignStats()
    {
        $body = ['ok' => true];
        [$client, $getHistory] = $this->makeClientWithHistoryPair([
            new Response(200, [], json_encode($body)),
            new Response(200, [], json_encode($body)),
        ]);

        // Call summary and list which will emit two GET requests
        // Request summary with explicit filter flags
        $this->assertSame($body, $client->reporting->summary('2025-01-01', '2025-01-31', '1', '1', '1', '1', '1', '0', '0'));
        $this->assertSame($body, $client->reporting->list('2025-01-01', '2025-01-31', 'delivered', 'campaign:summer'));

        $history = $getHistory();
        $this->assertCount(2, $history);

        // First request: summary
        $req1 = $history[0]['request'];
        $this->assertSame('GET', $req1->getMethod());
        $this->assertStringContainsString('reporting/summary/2025-01-01/2025-01-31', (string) $req1->getUri());

        // Parse query to assert api key (auth) and default params are present
        parse_str($req1->getUri()->getQuery(), $q1);
        $this->assertArrayHasKey('auth', $q1);
        // The summary filters should be present and set to our string flags
        $this->assertSame('1', $q1['received']);
        $this->assertSame('1', $q1['producing']);
        $this->assertSame('1', $q1['handed_over']);
        $this->assertSame('1', $q1['local_delivery']);
        $this->assertSame('1', $q1['delivered']);
        $this->assertSame('0', $q1['returned']);
        $this->assertSame('0', $q1['cancelled']);

        // Second request: list
        $req2 = $history[1]['request'];
        $this->assertSame('GET', $req2->getMethod());
        $this->assertStringContainsString('reporting/list/2025-01-01/2025-01-31/delivered/campaign:summer', (string) $req2->getUri());
        parse_str($req2->getUri()->getQuery(), $q2);
        $this->assertArrayHasKey('auth', $q2);
    }

    public function testSummaryThrowsOnApiError()
    {
        $this->expectException(\Phannp\Exceptions\ApiException::class);

        // Simulate a 400 Bad Request response
        $client = $this->makeClient([new \GuzzleHttp\Psr7\Response(400, [], json_encode(['error' => 'Bad Request']))]);

        // Use valid dates so the request is sent and the 400 response triggers ApiException
        $client->reporting->summary('2025-01-01', '2025-01-31');
    }

    public function testSummaryNetworkErrorThrowsApiException()
    {
        $this->expectException(\Phannp\Exceptions\ApiException::class);

        // Simulate a network error (RequestException with no response)
        $ex = new \GuzzleHttp\Exception\RequestException(
            'Network failure',
            new \GuzzleHttp\Psr7\Request('GET', '/'),
        );

        $client = $this->makeClient([$ex]);

        $client->reporting->summary('2025-01-01', '2025-01-31');
    }

    public function testSummaryApiErrorProvidesJson()
    {
        $body = ['errors' => ['message' => 'Invalid date range'], 'code' => 400];
        $client = $this->makeClient([new \GuzzleHttp\Psr7\Response(400, [], json_encode($body))]);

        try {
            // Use valid dates so the client sends the request and the mocked 400 is returned
            $client->reporting->summary('2025-01-01', '2025-01-31');
            $this->fail('Expected ApiException');
        } catch (\Phannp\Exceptions\ApiException $e) {
            $json = $e->getResponseJson();
            $this->assertIsArray($json);
            $this->assertArrayHasKey('errors', $json);
            $this->assertSame('Invalid date range', $json['errors']['message']);
        }
    }

    public function testSummaryClientSideValidation()
    {
        $this->expectException(\InvalidArgumentException::class);

        $client = $this->makeClient();

        // Pass an array where a string flag is expected to provoke a TypeError
        $client->reporting->summary('2025-01-01', 'not-a-date', '1');
    }
}
