<?php

namespace Phannp\Tests\Resources;

use Phannp\Tests\TestCase;
use GuzzleHttp\Psr7\Response;

class GroupsTest extends TestCase
{
    public function testCrudAndAddRemoveRecipient()
    {
        $body = ['ok' => true];
        $client = $this->makeClient([
            new Response(200, [], json_encode($body)),
            new Response(200, [], json_encode($body)),
            new Response(200, [], json_encode($body)),
            new Response(200, [], json_encode($body)),
            new Response(200, [], json_encode($body)),
            new Response(200, [], json_encode($body)),
        ]);

        $this->assertSame($body, $client->groups->create(['name' => 'G']));
        $this->assertSame($body, $client->groups->get(1));
        $this->assertSame($body, $client->groups->list());
        $this->assertSame($body, $client->groups->delete(1));
        $this->assertSame($body, $client->groups->addRecipient(1, 2));
        $this->assertSame($body, $client->groups->removeRecipient(1, 2));
    }
}
