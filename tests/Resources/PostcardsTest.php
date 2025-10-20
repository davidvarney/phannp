<?php

namespace Phannp\Tests\Resources;

use Phannp\Tests\TestCase;
use GuzzleHttp\Psr7\Response;

class PostcardsTest extends TestCase
{
    public function testCreateGetCancel()
    {
        $body = ['ok' => true];
        $client = $this->makeClient([
            new Response(200, [], json_encode($body)),
            new Response(200, [], json_encode($body)),
            new Response(200, [], json_encode($body)),
        ]);

    // create expects an array with a 'size' key
    $this->assertSame($body, $client->postcards->create(['size' => '4x6']));
        $this->assertSame($body, $client->postcards->get(123));
        // list() was removed from the resource
        $this->assertSame($body, $client->postcards->cancel(123));
    }

    public function testCreateSendsPostAndRequiresSize()
    {
        // Prepare a mock response that the SDK will return
        [$client, $getHistory] = $this->makeClientWithHistoryPair([new Response(200, [], json_encode(['ok' => true]))]);

        // Call the resource method
        $result = $client->postcards->create(['size' => '4x6']);

        // Assert we got the parsed response
        $this->assertIsArray($result);
        $this->assertArrayHasKey('ok', $result);
        $this->assertTrue($result['ok']);

        // Inspect the outgoing request
        $history = $getHistory();
        $this->assertCount(1, $history, 'Expected one outgoing request');

        $entry = $history[0];
        $request = $entry['request'];

        // Ensure it was a POST to the postcards/create endpoint
        $this->assertSame('POST', $request->getMethod());
        $this->assertStringContainsString('postcards/create', (string)$request->getUri());

        // For form-encoded body, ensure size param was sent
        $form = $this->getFormParams($request);
        $this->assertArrayHasKey('size', $form);
        $this->assertSame('4x6', $form['size']);

        // Ensure api_key is present in the request URI query
        parse_str($request->getUri()->getQuery(), $q);
        $this->assertArrayHasKey('api_key', $q);
        $this->assertSame('test_api_key', $q['api_key']);
    }

    public function testCreateThrowsWhenSizeMissing()
    {
        $this->expectException(\InvalidArgumentException::class);

        $client = $this->makeClient();
        $client->postcards->create([]);
    }
}
