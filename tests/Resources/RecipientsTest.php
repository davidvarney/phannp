<?php

namespace Phannp\Tests\Resources;

use Phannp\Tests\TestCase;
use GuzzleHttp\Psr7\Response;

class RecipientsTest extends TestCase
{
    public function testCrud()
    {
        $body = ['ok' => true];
        // We'll use a history pair to inspect the 'list' GET request
        [$client, $getHistory] = $this->makeClientWithHistoryPair([
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

    $history = $getHistory();
    // Ensure the list() produced a GET request we can inspect
    $this->assertNotEmpty($history);
    $last = end($history);
    $req = $last['request'];
    $this->assertSame('GET', $req->getMethod());

    // The Recipients::list uses path + query for group and pagination via query params
    $this->assertStringContainsString('recipients/list', (string) $req->getUri());
    parse_str($req->getUri()->getQuery(), $q);
    $this->assertArrayHasKey('auth', $q);
    $this->assertSame('0', $q['group_id']);
    $this->assertSame('0', $q['offset']);
    $this->assertSame('10', $q['limit']);

        $this->assertSame($body, $client->recipients->delete(1));

        // Import expects file path or URL, group id, duplicate handling, no_headings flag, and mappings string
        // realistic import invocation: CSV path, group id 0, update duplicates, include headings, mappings
    $this->assertSame($body, $client->recipients->import('/tmp/recipients.csv', 0, 'update', false, 'firstname,lastname,company,address1,address2,city,postcode,country,email,phone'));
    }

    public function testListThrowsOnApiError()
    {
        $this->expectException(\Phannp\Exceptions\ApiException::class);

        $client = $this->makeClient([new \GuzzleHttp\Psr7\Response(400, [], json_encode(['error' => 'Bad Request']))]);

        $client->recipients->list(0, 0, 10);
    }

    public function testListNetworkErrorThrowsApiException()
    {
        $this->expectException(\Phannp\Exceptions\ApiException::class);

        $ex = new \GuzzleHttp\Exception\RequestException(
            'Network failure',
            new \GuzzleHttp\Psr7\Request('GET', '/'),
        );

        $client = $this->makeClient([$ex]);

        $client->recipients->list(0, 0, 10);
    }

    public function testListApiErrorProvidesJson()
    {
        $body = ['errors' => ['message' => 'Group not found'], 'code' => 404];
        $client = $this->makeClient([new \GuzzleHttp\Psr7\Response(404, [], json_encode($body))]);

        try {
            $client->recipients->list(9999, 0, 10);
            $this->fail('Expected ApiException');
        } catch (\Phannp\Exceptions\ApiException $e) {
            $json = $e->getResponseJson();
            $this->assertIsArray($json);
            $this->assertArrayHasKey('errors', $json);
            $this->assertSame('Group not found', $json['errors']['message']);
        }
    }

    public function testCreateThrowsOnInvalidTypes()
    {
        $this->expectException(\InvalidArgumentException::class);

        $client = $this->makeClient();

        // Pass null for a required string parameter (firstname) to provoke a TypeError
        $client->recipients->create(null, 'Smith', '1 Test St', '', '', 'City', '12345', 'GB', 'a@b.com', '012345', 'ref', 0, 'update', 'standard');
    }

    public function testCreateRejectsInvalidEmail()
    {
        $this->expectException(\InvalidArgumentException::class);

        $client = $this->makeClient();

        $client->recipients->create('Alice', 'Smith', '1 Test St', '', '', 'City', '12345', 'GB', 'not-an-email', '012345', 'ref', 0, 'update', 'standard');
    }

    public function testCreateRejectsInvalidCountry()
    {
        $this->expectException(\InvalidArgumentException::class);

        $client = $this->makeClient();

        $client->recipients->create('Alice', 'Smith', '1 Test St', '', '', 'City', '12345', 'ZZ', 'alice@example.com', '012345', 'ref', 0, 'update', 'standard');
    }

    public function testCreateRejectsInvalidPhone()
    {
        $this->expectException(\InvalidArgumentException::class);

        $client = $this->makeClient();

        $client->recipients->create('Alice', 'Smith', '1 Test St', '', '', 'City', '12345', 'GB', 'alice@example.com', '+44 79-11abc3456', 'ref', 0, 'update', 'standard');
    }

    public function testCreateNormalizesPhoneAndPostcode()
    {
        $body = ['ok' => true];
        [$client, $getHistory] = $this->makeClientWithHistoryPair([new \GuzzleHttp\Psr7\Response(200, [], json_encode($body))]);

        $this->assertSame($body, $client->recipients->create('Alice', 'Smith', '1 Test St', '', '', 'London', ' nw1 6xe ', 'GB', 'alice@example.com', '+44 (7911) 123456', 'ref', 0, 'update', 'standard'));

        $history = $getHistory();
        $this->assertCount(1, $history);
        $req = $history[0]['request'];
        $this->assertSame('POST', $req->getMethod());
        $bodyString = (string) $req->getBody();
        parse_str($bodyString, $parsed);
        $this->assertArrayHasKey('postcode', $parsed);
        $this->assertSame('NW1 6XE', $parsed['postcode']);
        $this->assertArrayHasKey('phone_number', $parsed);
        $this->assertSame('+447911123456', $parsed['phone_number']);
    }
}
