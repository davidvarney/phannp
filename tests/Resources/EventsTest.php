<?php

namespace Phannp\Tests\Resources;

use Phannp\Tests\TestCase;
use GuzzleHttp\Psr7\Response;

class EventsTest extends TestCase
{
    public function testCreate()
    {
        $body = ['ok' => true];
        [$client, $getHistory] = $this->makeClientWithHistoryPair([new Response(200, [], json_encode($body))]);

        $this->assertSame($body, $client->events->create('recipient-123', 'PURCHASE', '49.99', true, '{"sku":"X"}', 'ref-1'));

        $history = $getHistory();
        $this->assertCount(1, $history);

        $request = $history[0]['request'];
        $this->assertSame('POST', $request->getMethod());
        $this->assertStringContainsString('events/create', (string) $request->getUri()->getPath());

        $bodyString = (string) $request->getBody();
        $this->assertStringContainsString('recipient_id=recipient-123', $bodyString);
        $this->assertStringContainsString('name=PURCHASE', $bodyString);
        $this->assertStringContainsString('value=49.99', $bodyString);
        // conversion boolean should be serialized (1)
        $this->assertStringContainsString('conversion=1', $bodyString);
        $this->assertStringContainsString('data=%7B%22sku%22%3A%22X%22%7D', $bodyString);
        $this->assertStringContainsString('ref=ref-1', $bodyString);
        // API key is injected as auth[0]
        $this->assertStringContainsString('auth%5B0%5D=test_api_key', $bodyString);
    }
}
