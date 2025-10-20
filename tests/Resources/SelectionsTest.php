<?php

namespace Phannp\Tests\Resources;

use Phannp\Tests\TestCase;
use GuzzleHttp\Psr7\Response;

class SelectionsTest extends TestCase
{
    public function testCreateGetList()
    {
        $body = ['ok' => true];
        [$client, $getHistory] = $this->makeClientWithHistoryPair([
            new Response(200, [], json_encode($body)),
        ]);

        // Selections::new(groupId, name, filters)
        $this->assertSame($body, $client->selections->new(1, 'Active', 'total_spent:::more_than:::300'));

        $history = $getHistory();
        $this->assertCount(1, $history);
        $req = $history[0]['request'];
        $this->assertSame('GET', $req->getMethod());
    $this->assertStringContainsString('selections/new', (string) $req->getUri());
    // Ensure api_key is present in the query
    parse_str($req->getUri()->getQuery(), $q);
    $this->assertArrayHasKey('api_key', $q);
    $this->assertSame('test_api_key', $q['api_key']);
    }

    public function testNewApiErrorProvidesJson()
    {
        $body = ['errors' => ['message' => 'Invalid filter'], 'code' => 400];
        $client = $this->makeClient([new Response(400, [], json_encode($body))]);

        try {
            $client->selections->new(1, 'Active', 'bad_filter');
            $this->fail('Expected ApiException');
        } catch (\Phannp\Exceptions\ApiException $e) {
            $json = $e->getResponseJson();
            $this->assertIsArray($json);
            $this->assertSame('Invalid filter', $json['errors']['message']);
        }
    }
}
