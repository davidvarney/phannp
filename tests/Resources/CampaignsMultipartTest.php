<?php

namespace Phannp\Tests\Resources;

use Phannp\Tests\TestCase;
use GuzzleHttp\Psr7\Response;

class CampaignsMultipartTest extends TestCase
{
    public function testCreateUsesMultipartWhenFileResourceProvided()
    {
        $body = ['ok' => true];

        // Create a temporary resource for upload
        $resource = fopen('php://temp', 'r+');
        fwrite($resource, 'PDFDATA');
        rewind($resource);

        [$client, $getHistory] = $this->makeClientWithHistoryPair([new Response(200, [], json_encode($body))]);

        $this->assertSame($body, $client->campaigns->create([
            'name' => 'Multipart Campaign',
            'type' => 'a6-postcard',
            'template_id' => 2,
            'file' => $resource,
        ]));

        $history = $getHistory();
        $this->assertCount(1, $history);

        $transaction = $history[0];
        $request = $transaction['request'];

        // Multipart requests should include a Content-Type header with multipart/form-data
        $this->assertTrue($request->hasHeader('Content-Type'));
        $ct = $request->getHeaderLine('Content-Type');
        $this->assertStringContainsString('multipart/form-data', $ct);

        // Close resource
        fclose($resource);
    }
}
