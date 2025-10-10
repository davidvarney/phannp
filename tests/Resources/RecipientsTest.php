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

        // Create with required recipient fields (firstname, lastname, address1, ...)
        // realistic sample recipient data
        $this->assertSame($body, $client->recipients->create(
            'Alice', 'Smith', '23 Baker Street', 'Flat 4', '', 'London', 'NW1 6XE', 'GB', 'alice.smith@example.co.uk', '+447911123456', 'alice-001', 0, 'update', 'standard'
        ));

        $this->assertSame($body, $client->recipients->get(1));

        // List recipients for a group (groupId 0 means all) with pagination
        $this->assertSame($body, $client->recipients->list(0, 0, 10));

        $this->assertSame($body, $client->recipients->delete(1));

        // Import expects file path or URL, group id, duplicate handling, no_headings flag, and mappings string
        // realistic import invocation: CSV path, group id 0, update duplicates, include headings, mappings
        $this->assertSame($body, $client->recipients->import('/tmp/recipients.csv', 0, 'update', false, 'firstname,lastname,company,address1,address2,city,postcode,country,email,phone'));
    }
}
