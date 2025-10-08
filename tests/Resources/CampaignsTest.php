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

        $this->assertSame($body, $client->campaigns->create(['a' => 1]));
        $this->assertSame($body, $client->campaigns->get(1));
        $this->assertSame($body, $client->campaigns->list());
        $this->assertSame($body, $client->campaigns->delete(1));
    }
}
