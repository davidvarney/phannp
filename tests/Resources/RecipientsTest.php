<?php

namespace Phannp\Tests\Resources;

use Phannp\Tests\TestCase;
use GuzzleHttp\Psr7\Response;

class RecipientsTest extends TestCase
{
    public function testCrud()
    {
        $body = ['ok' => true];
        $client = $this->makeClient([
            new Response(200, [], json_encode($body)),
            new Response(200, [], json_encode($body)),
            new Response(200, [], json_encode($body)),
            new Response(200, [], json_encode($body)),
            new Response(200, [], json_encode($body)),
        ]);

        $this->assertSame($body, $client->recipients->create(['name' => 'A']));
        $this->assertSame($body, $client->recipients->get(1));
        $this->assertSame($body, $client->recipients->list());
        $this->assertSame($body, $client->recipients->delete(1));
        $this->assertSame($body, $client->recipients->import(['rows' => []]));
    }
}
