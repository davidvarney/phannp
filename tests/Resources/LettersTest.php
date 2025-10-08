<?php

namespace Phannp\Tests\Resources;

use Phannp\Tests\TestCase;
use GuzzleHttp\Psr7\Response;

class LettersTest extends TestCase
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

        $this->assertSame($body, $client->letters->create(['a' => 1]));
        $this->assertSame($body, $client->letters->get(1));
        $this->assertSame($body, $client->letters->list());
        $this->assertSame($body, $client->letters->cancel(1));
    }
}
