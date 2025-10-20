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
}
