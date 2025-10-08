<?php

namespace Phannp\Tests\Resources;

use Phannp\Tests\TestCase;
use GuzzleHttp\Psr7\Response;

class EventsTest extends TestCase
{
    public function testListAndGet()
    {
        $body = ['ok' => true];
        $client = $this->makeClient([new Response(200, [], json_encode($body)), new Response(200, [], json_encode($body))]);

        $this->assertSame($body, $client->events->list());
        $this->assertSame($body, $client->events->get(1));
    }
}
