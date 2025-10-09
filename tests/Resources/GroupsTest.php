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

        // Create a group by name
        $this->assertSame($body, $client->groups->create('G'));

        // List groups
        $this->assertSame($body, $client->groups->list());

        // Delete the group
        $this->assertSame($body, $client->groups->delete(1));

        // Add and remove recipients (API expects comma-separated string of IDs)
        $this->assertSame($body, $client->groups->addRecipients(1, '2'));
        $this->assertSame($body, $client->groups->removeRecipients(1, '2'));

        // Recalculate group recipient count
        $this->assertSame($body, $client->groups->recalculate(1));
    }
}
