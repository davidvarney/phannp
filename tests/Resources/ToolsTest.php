<?php

namespace Phannp\Tests\Resources;

use Phannp\Tests\TestCase;
use GuzzleHttp\Psr7\Response;

class ToolsTest extends TestCase
{
    public function testHelpers()
    {
        $body = ['ok' => true];
        [$client, $getHistory] = $this->makeClientWithHistoryPair([
            new Response(200, [], json_encode($body)),
            new Response(200, [], json_encode($body)),
            new Response(200, [], json_encode($body)),
        ]);

        // Call the three helpers which will emit GET requests
        $this->assertSame($body, $client->tools->qrcodeCreate('https://example.com', 300));
        $this->assertSame($body, $client->tools->pdfMerge(['https://example.com/a.pdf', 'https://example.com/b.pdf']));
        $this->assertSame($body, $client->tools->getTemplates());

        $history = $getHistory();
        $this->assertCount(3, $history);

        // qrcodeCreate: first request should include data and size in query
        $req1 = $history[0]['request'];
        $this->assertSame('GET', $req1->getMethod());
        $this->assertStringContainsString('qrcode/create', (string) $req1->getUri());
        parse_str($req1->getUri()->getQuery(), $q1);
        $this->assertArrayHasKey('data', $q1);
        $this->assertArrayHasKey('size', $q1);
        $this->assertSame('https://example.com', $q1['data']);
        $this->assertSame('300', $q1['size']);

        // pdfMerge: second request should include files[] entries
        $req2 = $history[1]['request'];
        $this->assertStringContainsString('pdf/merge', (string) $req2->getUri());
        parse_str($req2->getUri()->getQuery(), $q2);
        $this->assertArrayHasKey('files', $q2);
        $this->assertIsArray($q2['files']);
        $this->assertSame(['https://example.com/a.pdf', 'https://example.com/b.pdf'], $q2['files']);

        // getTemplates: third should call templates/list
        $req3 = $history[2]['request'];
        $this->assertStringContainsString('templates/list', (string) $req3->getUri());
    }

    public function testPdfMergeEdgeCases()
    {
        $body = ['ok' => true];
        // Empty files array is now invalid and should raise an InvalidArgumentException
        [$client] = $this->makeClientWithHistoryPair([new Response(200, [], json_encode($body))]);
        $this->expectException(\InvalidArgumentException::class);
        $client->tools->pdfMerge([]);

        // Non-array input should raise a TypeError due to method signature
        $this->expectException(\TypeError::class);
        /* @phpstan-ignore-next-line */ $client->tools->pdfMerge('not-an-array');
    }

    public function testPdfMergeQueryEncodesFilesAsRepeatedParams()
    {
        $body = ['ok' => true];
        [$client, $getHistory] = $this->makeClientWithHistoryPair([new Response(200, [], json_encode($body))]);

        $this->assertSame($body, $client->tools->pdfMerge(['https://example.com/a.pdf', 'https://example.com/b.pdf']));

        $history = $getHistory();
        $this->assertCount(1, $history);
        $rawQuery = $history[0]['request']->getUri()->getQuery();

    // Expect the raw query to contain files parameters (either indexed files[0]=.. or unindexed files[]=..)
    $this->assertStringContainsString('files%5B', $rawQuery);
    $this->assertStringContainsString(urlencode('https://example.com/a.pdf'), $rawQuery);
    $this->assertStringContainsString(urlencode('https://example.com/b.pdf'), $rawQuery);
    }
}
