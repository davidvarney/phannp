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
        parse_str($query, $q);
        $this->assertArrayHasKey('id', $q);
        $this->assertSame('123', $q['id']);
        $this->assertArrayHasKey('api_key', $q);
        $this->assertSame('test_api_key', $q['api_key']);
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
        parse_str($query, $q);
        $this->assertArrayHasKey('id', $q);
        $this->assertSame('321', $q['id']);
        $this->assertArrayHasKey('api_key', $q);
        $this->assertSame('test_api_key', $q['api_key']);
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

        // API key is appended to the endpoint as a query parameter
        $query = $request->getUri()->getQuery();
        $this->assertStringContainsString('api_key=test_api_key', $query);

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
        parse_str($query, $q);
        $this->assertArrayHasKey('start', $q);
        $this->assertSame('2025-10-01', $q['start']);
        $this->assertArrayHasKey('end', $q);
        $this->assertSame('2025-10-31', $q['end']);
        $this->assertArrayHasKey('api_key', $q);
        $this->assertSame('test_api_key', $q['api_key']);
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
    $this->assertStringContainsString('api_key=test_api_key', $query);
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
        parse_str($query, $q);
        $this->assertArrayHasKey('start', $q);
        $this->assertSame('2025-10-05', $q['start']);
        $this->assertArrayHasKey('end', $q);
        $this->assertSame('2025-11-04', $q['end']);
        $this->assertArrayHasKey('api_key', $q);
        $this->assertSame('test_api_key', $q['api_key']);
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
        parse_str($query, $q);
        $this->assertArrayHasKey('start', $q);
        $this->assertSame('2025-10-01', $q['start']);
        $this->assertArrayHasKey('end', $q);
        $this->assertSame('2025-10-31', $q['end']);
        $this->assertArrayHasKey('api_key', $q);
        $this->assertSame('test_api_key', $q['api_key']);
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
        // API key should be in the request query (appended to endpoint)
        $query = $request->getUri()->getQuery();
        parse_str($query, $q);
        $this->assertArrayHasKey('api_key', $q);
        $this->assertSame('test_api_key', $q['api_key']);

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

    public function testMultipartCreateSendsFileFrontBack()
    {
        $body = ['ok' => true];

        // Create temp files for file, front, back
        // Use filenames with extensions so the parser can expose them
        $tmpFile = tempnam(sys_get_temp_dir(), 'phannp_campaign_') . '.pdf';
        $tmpFront = tempnam(sys_get_temp_dir(), 'phannp_campaign_') . '.jpg';
        $tmpBack = tempnam(sys_get_temp_dir(), 'phannp_campaign_') . '.jpg';

        file_put_contents($tmpFile, 'campaign-file');
        file_put_contents($tmpFront, 'campaign-front');
        file_put_contents($tmpBack, 'campaign-back');

        [$client, $getHistory] = $this->makeClientWithHistoryPair([
            new \GuzzleHttp\Psr7\Response(200, [], json_encode($body)),
        ]);

        try {
            $data = [
                'name' => 'Test',
                'type' => 'a6-postcard',
                'template_id' => 2,
                'file' => $tmpFile,
                'front' => $tmpFront,
                'back' => $tmpBack,
            ];

            $this->assertSame($body, $client->campaigns->create($data));

            $history = $getHistory();
            $this->assertCount(1, $history);
            $request = $history[0]['request'];

            $this->assertStringContainsString('multipart/form-data', $request->getHeaderLine('Content-Type'));

            $parts = $this->parseMultipartBody($request);

            $map = [];
            foreach ($parts as $p) {
                $map[$p['name']] = $p;
            }

            $this->assertArrayHasKey('file', $map);
            $this->assertStringContainsString('campaign-file', $map['file']['body']);
            $this->assertSame(basename($tmpFile), $map['file']['filename']);
            $this->assertNotNull($map['file']['content_type']);

            $this->assertArrayHasKey('front', $map);
            $this->assertStringContainsString('campaign-front', $map['front']['body']);
            $this->assertSame(basename($tmpFront), $map['front']['filename']);
            $this->assertNotNull($map['front']['content_type']);

            $this->assertArrayHasKey('back', $map);
            $this->assertStringContainsString('campaign-back', $map['back']['body']);
            $this->assertSame(basename($tmpBack), $map['back']['filename']);
            $this->assertNotNull($map['back']['content_type']);
        } finally {
            @unlink($tmpFile);
            @unlink($tmpFront);
            @unlink($tmpBack);
        }
    }

    public function testMultipartCreateWithResources()
    {
        $body = ['ok' => true];

        // create temp files and open as resources
        $tmpFile = tempnam(sys_get_temp_dir(), 'phannp_campaign_') . '.pdf';
        $tmpFront = tempnam(sys_get_temp_dir(), 'phannp_campaign_') . '.jpg';
        $tmpBack = tempnam(sys_get_temp_dir(), 'phannp_campaign_') . '.jpg';

        file_put_contents($tmpFile, 'campaign-file');
        file_put_contents($tmpFront, 'campaign-front');
        file_put_contents($tmpBack, 'campaign-back');

        $f1 = fopen($tmpFile, 'r');
        $f2 = fopen($tmpFront, 'r');
        $f3 = fopen($tmpBack, 'r');

        [$client, $getHistory] = $this->makeClientWithHistoryPair([
            new \GuzzleHttp\Psr7\Response(200, [], json_encode($body)),
        ]);

        try {
            $data = [
                'name' => 'Test',
                'type' => 'a6-postcard',
                'template_id' => 2,
                'file' => $f1,
                'front' => $f2,
                'back' => $f3,
            ];

            $this->assertSame($body, $client->campaigns->create($data));

            $history = $getHistory();
            $this->assertCount(1, $history);
            $request = $history[0]['request'];

            $this->assertStringContainsString('multipart/form-data', $request->getHeaderLine('Content-Type'));

            $parts = $this->parseMultipartBody($request);

            // When sending resources, filenames may not be present; ensure bodies are present and content_type exists
            $foundNames = array_column($parts, 'name');
            $this->assertTrue(in_array('file', $foundNames, true), 'Expected part "file" not found');
            $this->assertTrue(in_array('front', $foundNames, true), 'Expected part "front" not found');
            $this->assertTrue(in_array('back', $foundNames, true), 'Expected part "back" not found');

            foreach ($parts as $p) {
                if (in_array($p['name'], ['file','front','back'], true)) {
                    $this->assertNotEmpty($p['body']);
                    // Some environments include a filename for resource parts; allow either null or the original basename
                    $expected = null;
                    if ($p['name'] === 'file') {
                        $expected = basename($tmpFile);
                    } elseif ($p['name'] === 'front') {
                        $expected = basename($tmpFront);
                    } elseif ($p['name'] === 'back') {
                        $expected = basename($tmpBack);
                    }

                    if ($p['filename'] !== null) {
                        $this->assertSame($expected, $p['filename']);
                    }

                    $this->assertNotNull($p['content_type']);
                }
            }
        } finally {
            @fclose($f1);
            @fclose($f2);
            @fclose($f3);
            @unlink($tmpFile);
            @unlink($tmpFront);
            @unlink($tmpBack);
        }
    }

    public function testMultipartCreateMixedResourceFileOnly()
    {
        $body = ['ok' => true];

        // create temp files; we'll open 'file' as resource and pass front/back as paths
        $tmpFile = tempnam(sys_get_temp_dir(), 'phannp_campaign_') . '.pdf';
        $tmpFront = tempnam(sys_get_temp_dir(), 'phannp_campaign_') . '.jpg';
        $tmpBack = tempnam(sys_get_temp_dir(), 'phannp_campaign_') . '.jpg';

        file_put_contents($tmpFile, 'campaign-file');
        file_put_contents($tmpFront, 'campaign-front');
        file_put_contents($tmpBack, 'campaign-back');

        $f1 = fopen($tmpFile, 'r');

        [$client, $getHistory] = $this->makeClientWithHistoryPair([
            new \GuzzleHttp\Psr7\Response(200, [], json_encode($body)),
        ]);

        try {
            $data = [
                'name' => 'Test',
                'type' => 'a6-postcard',
                'template_id' => 2,
                'file' => $f1,
                'front' => $tmpFront,
                'back' => $tmpBack,
            ];

            $this->assertSame($body, $client->campaigns->create($data));

            $history = $getHistory();
            $this->assertCount(1, $history);
            $request = $history[0]['request'];

            $parts = $this->parseMultipartBody($request);
            $map = [];
            foreach ($parts as $p) {
                $map[$p['name']] = $p;
            }

            // file may have filename or not; allow either, and ensure bodies and content types exist
            $this->assertArrayHasKey('file', $map);
            $this->assertStringContainsString('campaign-file', $map['file']['body']);
            if ($map['file']['filename'] !== null) {
                $this->assertSame(basename($tmpFile), $map['file']['filename']);
            }
            $this->assertNotNull($map['file']['content_type']);

            // front/back were passed as paths, should have filenames
            $this->assertArrayHasKey('front', $map);
            $this->assertSame(basename($tmpFront), $map['front']['filename']);
            $this->assertArrayHasKey('back', $map);
            $this->assertSame(basename($tmpBack), $map['back']['filename']);
        } finally {
            @fclose($f1);
            @unlink($tmpFile);
            @unlink($tmpFront);
            @unlink($tmpBack);
        }
    }

    public function testMultipartCreateMixedResourceFrontOnly()
    {
        $body = ['ok' => true];

        // create temp files; front will be resource, others paths
        $tmpFile = tempnam(sys_get_temp_dir(), 'phannp_campaign_') . '.pdf';
        $tmpFront = tempnam(sys_get_temp_dir(), 'phannp_campaign_') . '.jpg';
        $tmpBack = tempnam(sys_get_temp_dir(), 'phannp_campaign_') . '.jpg';

        file_put_contents($tmpFile, 'campaign-file');
        file_put_contents($tmpFront, 'campaign-front');
        file_put_contents($tmpBack, 'campaign-back');

        $fFront = fopen($tmpFront, 'r');

        [$client, $getHistory] = $this->makeClientWithHistoryPair([
            new \GuzzleHttp\Psr7\Response(200, [], json_encode($body)),
        ]);

        try {
            $data = [
                'name' => 'Test',
                'type' => 'a6-postcard',
                'template_id' => 2,
                'file' => $tmpFile,
                'front' => $fFront,
                'back' => $tmpBack,
            ];

            $this->assertSame($body, $client->campaigns->create($data));

            $history = $getHistory();
            $this->assertCount(1, $history);
            $request = $history[0]['request'];

            $parts = $this->parseMultipartBody($request);
            $map = [];
            foreach ($parts as $p) {
                $map[$p['name']] = $p;
            }

            // file/back were paths
            $this->assertSame(basename($tmpFile), $map['file']['filename']);
            $this->assertSame(basename($tmpBack), $map['back']['filename']);

            // front is a resource: either null filename or matches basename
            $this->assertArrayHasKey('front', $map);
            if ($map['front']['filename'] !== null) {
                $this->assertSame(basename($tmpFront), $map['front']['filename']);
            }
            $this->assertNotNull($map['front']['content_type']);
        } finally {
            @fclose($fFront);
            @unlink($tmpFile);
            @unlink($tmpFront);
            @unlink($tmpBack);
        }
    }
}
