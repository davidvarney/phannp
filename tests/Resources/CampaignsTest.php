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
    $this->assertStringContainsString('auth%5B0%5D=test_api_key', $query);
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
    $this->assertStringContainsString('auth%5B0%5D=test_api_key', $query);
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
    $this->assertStringContainsString('auth%5B0%5D=test_api_key', $bodyString);

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

    public function testAvailableDates()
    {
        $body = ['dates' => ['2025-10-10', '2025-10-11']];
        [$client, $getHistory] = $this->makeClientWithHistoryPair([new \GuzzleHttp\Psr7\Response(200, [], json_encode($body))]);

        $this->assertSame($body, $client->campaigns->availableDates('2025-10-01', '2025-10-31'));

        $history = $getHistory();
        $this->assertCount(1, $history);

        $request = $history[0]['request'];
        $this->assertSame('GET', $request->getMethod());
        $this->assertStringContainsString('campaigns/available-dates', (string) $request->getUri()->getPath());

        $query = $request->getUri()->getQuery();
        $this->assertStringContainsString('start=2025-10-01', $query);
        $this->assertStringContainsString('end=2025-10-31', $query);
    $this->assertStringContainsString('auth%5B0%5D=test_api_key', $query);
    }

    public function testAvailableDatesThrowsApiExceptionOn4xx()
    {
        [$client] = $this->makeClientWithHistoryPair([new \GuzzleHttp\Psr7\Response(400, [], json_encode(['error' => 'bad']))]);

        try {
            $client->campaigns->availableDates('2025-10-01', '2025-10-31');
            $this->fail('Expected ApiException was not thrown');
        } catch (\Phannp\Exceptions\ApiException $e) {
            $this->assertSame(400, $e->getStatusCode());
            $this->assertStringContainsString('error', $e->getResponseBody());
        }
    }

    public function testAvailableDatesThrowsApiExceptionOn5xx()
    {
        [$client] = $this->makeClientWithHistoryPair([new \GuzzleHttp\Psr7\Response(500, [], 'Server error')]);

        try {
            $client->campaigns->availableDates('2025-10-01', '2025-10-31');
            $this->fail('Expected ApiException was not thrown');
        } catch (\Phannp\Exceptions\ApiException $e) {
            $this->assertSame(500, $e->getStatusCode());
            $this->assertStringContainsString('Server error', $e->getResponseBody());
        }
    }

    public function testAvailableDatesDefaults()
    {
        $body = ['dates' => ['2025-10-10']];
        [$client, $getHistory] = $this->makeClientWithHistoryPair([
            new \GuzzleHttp\Psr7\Response(200, [], json_encode($body))
        ], ['date_provider' => function () { return '2025-10-01'; }]);

        $this->assertSame($body, $client->campaigns->availableDates());

        $history = $getHistory();
        $this->assertCount(1, $history);

        $request = $history[0]['request'];
        $this->assertSame('GET', $request->getMethod());
        $this->assertStringContainsString('campaigns/available-dates', (string) $request->getUri()->getPath());

        $query = $request->getUri()->getQuery();
        $this->assertStringContainsString('start=2025-10-01', $query);
        $this->assertStringContainsString('end=2025-10-31', $query);
    $this->assertStringContainsString('auth%5B0%5D=test_api_key', $query);
    }

    public function testAvailableDatesWithStartOnlyDefaultsEnd()
    {
        $body = ['dates' => ['2025-10-10']];
        [$client, $getHistory] = $this->makeClientWithHistoryPair([
            new \GuzzleHttp\Psr7\Response(200, [], json_encode($body))
        ], ['date_provider' => function () { return '2025-10-01'; }]);

        // Provide only a start date. The SDK will supply an end date using
        // the client's date provider (today + 30 days) according to current
        // implementation.
        $this->assertSame($body, $client->campaigns->availableDates('2025-10-05'));

        $history = $getHistory();
        $this->assertCount(1, $history);

        $request = $history[0]['request'];
        $this->assertSame('GET', $request->getMethod());
        $this->assertStringContainsString('campaigns/available-dates', (string) $request->getUri()->getPath());

        $query = $request->getUri()->getQuery();
        // start should be the provided value
        $this->assertStringContainsString('start=2025-10-05', $query);
    // end is computed from the provided start date (start + 30 days)
    $this->assertStringContainsString('end=2025-11-04', $query);
    $this->assertStringContainsString('auth%5B0%5D=test_api_key', $query);
    }

    public function testAvailableDatesWithEndOnlyDefaultsStart()
    {
        $body = ['dates' => ['2025-09-01']];
        [$client, $getHistory] = $this->makeClientWithHistoryPair([
            new \GuzzleHttp\Psr7\Response(200, [], json_encode($body))
        ]);

        // Provide only an end date. The SDK should compute start = end - 30 days
        $this->assertSame($body, $client->campaigns->availableDates(null, '2025-10-31'));

        $history = $getHistory();
        $this->assertCount(1, $history);

        $request = $history[0]['request'];
        $this->assertSame('GET', $request->getMethod());
        $this->assertStringContainsString('campaigns/available-dates', (string) $request->getUri()->getPath());

        $query = $request->getUri()->getQuery();
        // start should be end - 30 days
        $this->assertStringContainsString('start=2025-10-01', $query);
        $this->assertStringContainsString('end=2025-10-31', $query);
    $this->assertStringContainsString('auth%5B0%5D=test_api_key', $query);
    }

    public function testAvailableDatesRejectsInvalidStart()
    {
        $this->expectException(\Phannp\Exceptions\PhannpException::class);
        $client = $this->makeClient();
        // invalid start format
        $client->campaigns->availableDates('2025-13-01', '2025-10-31');
    }

    public function testAvailableDatesRejectsInvalidEnd()
    {
        $this->expectException(\Phannp\Exceptions\PhannpException::class);
        $client = $this->makeClient();
        // invalid end format
        $client->campaigns->availableDates('2025-10-01', '2025-02-30');
    }

    public function testBookSuccess()
    {
        $body = ['ok' => true];
        [$client, $getHistory] = $this->makeClientWithHistoryPair([
            new \GuzzleHttp\Psr7\Response(200, [], json_encode($body))
        ]);

        $this->assertSame($body, $client->campaigns->book(77, '2025-10-20', true, false));

        $history = $getHistory();
        $this->assertCount(1, $history);

        $request = $history[0]['request'];
        $this->assertSame('POST', $request->getMethod());

        // Body should be form-encoded including parameters and api_key
        $bodyString = (string) $request->getBody();
        $this->assertStringContainsString('id=77', $bodyString);
        $this->assertStringContainsString('send_date=2025-10-20', $bodyString);
        $this->assertStringContainsString('next_available_date=1', $bodyString);
        $this->assertStringContainsString('use_balance=0', $bodyString);
    $this->assertStringContainsString('auth%5B0%5D=test_api_key', $bodyString);

        $this->assertTrue($request->hasHeader('Content-Type'));
        $ct = $request->getHeaderLine('Content-Type');
        $this->assertStringContainsString('application/x-www-form-urlencoded', $ct);
    }

    public function testBookRejectsInvalidDate()
    {
        $this->expectException(\Phannp\Exceptions\PhannpException::class);
        $client = $this->makeClient();
        $client->campaigns->book(1, '2025-02-30');
    }

    public function testBookThrowsApiExceptionOn4xx()
    {
        [$client] = $this->makeClientWithHistoryPair([
            new \GuzzleHttp\Psr7\Response(400, [], json_encode(['error' => 'bad']))
        ]);

        try {
            $client->campaigns->book(1, '2025-10-20');
            $this->fail('Expected ApiException was not thrown');
        } catch (\Phannp\Exceptions\ApiException $e) {
            $this->assertSame(400, $e->getStatusCode());
            $this->assertStringContainsString('error', $e->getResponseBody());
        }
    }

    public function testBookThrowsApiExceptionOn5xx()
    {
        [$client] = $this->makeClientWithHistoryPair([
            new \GuzzleHttp\Psr7\Response(500, [], 'Server error')
        ]);

        try {
            $client->campaigns->book(1, '2025-10-20');
            $this->fail('Expected ApiException was not thrown');
        } catch (\Phannp\Exceptions\ApiException $e) {
            $this->assertSame(500, $e->getStatusCode());
            $this->assertStringContainsString('Server error', $e->getResponseBody());
        }
    }
}
