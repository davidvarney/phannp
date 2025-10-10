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

    // Selections::new(groupId, name, filters)
    $this->assertSame($body, $client->selections->new(1, 'Active', 'total_spent:::more_than:::300'));
    }
}
