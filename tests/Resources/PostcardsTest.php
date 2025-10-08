<?php

namespace Phannp\Tests\Resources;

use Phannp\Tests\TestCase;
use GuzzleHttp\Psr7\Response;

class PostcardsTest extends TestCase
{
    public function testCreateGetListCancel()
    {
        $body = ['ok' => true];
        $client = $this->makeClient([
            new Response(200, [], json_encode($body)),
            new Response(200, [], json_encode($body)),
            new Response(200, [], json_encode($body)),
            new Response(200, [], json_encode($body)),
        ]);

        $this->assertSame($body, $client->postcards->create(['a' => 1]));
        $this->assertSame($body, $client->postcards->get(123));
        $this->assertSame($body, $client->postcards->list());
        $this->assertSame($body, $client->postcards->cancel(123));
    }
}
