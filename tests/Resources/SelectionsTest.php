<?php

namespace Phannp\Tests\Resources;

use Phannp\Tests\TestCase;
use GuzzleHttp\Psr7\Response;

class SelectionsTest extends TestCase
{
    public function testCreateGetList()
    {
        $body = ['ok' => true];
        $client = $this->makeClient([
            new Response(200, [], json_encode($body)),
            new Response(200, [], json_encode($body)),
            new Response(200, [], json_encode($body)),
        ]);

        $this->assertSame($body, $client->selections->create(['a' => 1]));
        $this->assertSame($body, $client->selections->get(1));
        $this->assertSame($body, $client->selections->list());
    }
}
