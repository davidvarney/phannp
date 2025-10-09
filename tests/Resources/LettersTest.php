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

    // create accepts either an existing recipient id or a new recipient array
    $this->assertSame($body, $client->letters->create(['a' => 1]));
    $this->assertSame($body, $client->letters->get(1));
    // Use post() to send a pre-merged PDF-based letter (country required)
    $this->assertSame($body, $client->letters->post('US'));
    $this->assertSame($body, $client->letters->cancel(1));
    }
}
