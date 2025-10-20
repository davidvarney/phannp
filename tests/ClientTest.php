<?php

namespace Phannp\Tests;

use GuzzleHttp\Psr7\Response;

class ClientTest extends TestCase
{
    public function testGetAndResourcesWired()
    {
        $body = ['user' => ['id' => 1, 'name' => 'Test']];
        $client = $this->makeClient([new Response(200, [], json_encode($body))]);

        $resp = $client->get('users/me');
        $this->assertSame($body, $resp);

        // resources should be instances and share the client
        $this->assertInstanceOf(\Phannp\Resources\Account::class, $client->account);
        $this->assertInstanceOf(\Phannp\Resources\Postcards::class, $client->postcards);
    }

    public function testPostPutDelete()
    {
        $body = ['result' => 'ok'];
        $client = $this->makeClient([
            new Response(200, [], json_encode($body)), // post
            new Response(200, [], json_encode($body)), // put
            new Response(200, [], json_encode($body)), // delete
        ]);

        $this->assertSame($body, $client->post('some/endpoint', ['a' => 1]));
        $this->assertSame($body, $client->put('some/endpoint', ['a' => 2]));
        $this->assertSame($body, $client->delete('some/endpoint'));
    }

    public function testAddApiKeyInjectsKey()
    {
        $client = $this->makeClient();

        $ref = new \ReflectionClass($client);
        $method = $ref->getMethod('addApiKey');
        $method->setAccessible(true);

        $data = ['foo' => 'bar'];
        $result = $method->invoke($client, $data);

        $this->assertArrayHasKey('api_key', $result);
        $this->assertSame('test_api_key', $result['api_key']);
        $this->assertSame('bar', $result['foo']);
    }
    
    public function testPostPutDeleteWithTrailingSlashAppendApiKey()
    {
        $body = ['ok' => true];
        [$client, $getHistory] = $this->makeClientWithHistoryPair([
            new Response(200, [], json_encode($body)),
            new Response(200, [], json_encode($body)),
            new Response(200, [], json_encode($body)),
        ]);

        $this->assertSame($body, $client->post('some/endpoint/', ['a' => 1]));
        $this->assertSame($body, $client->put('some/endpoint/', ['a' => 2]));
        $this->assertSame($body, $client->delete('some/endpoint/'));

        $history = $getHistory();
        $this->assertCount(3, $history);

        foreach ($history as $transaction) {
            $req = $transaction['request'];
            $this->assertStringContainsString('some/endpoint', (string) $req->getUri()->getPath());
            parse_str($req->getUri()->getQuery(), $q);
            $this->assertArrayHasKey('api_key', $q);
            $this->assertSame('test_api_key', $q['api_key']);
        }
    }

    public function testExistingQueryParamCollision()
    {
        $body = ['ok' => true];
        [$client, $getHistory] = $this->makeClientWithHistoryPair([
            new Response(200, [], json_encode($body)),
        ]);

        // Endpoint already has an api_key param set to a wrong value; client should override
        $this->assertSame($body, $client->post('some/endpoint?api_key=wrong&x=1', ['a' => 1]));

        $history = $getHistory();
        $this->assertCount(1, $history);
        $req = $history[0]['request'];
        parse_str($req->getUri()->getQuery(), $q);
        $this->assertSame('test_api_key', $q['api_key']);
        $this->assertSame('1', $q['x']);
    }

    public function testRootAndEmptyEndpoints()
    {
        $body = ['ok' => true];
        [$client, $getHistory] = $this->makeClientWithHistoryPair([
            new Response(200, [], json_encode($body)),
            new Response(200, [], json_encode($body)),
        ]);

        // Call root endpoint and empty endpoint
        $this->assertSame($body, $client->put('/', ['a' => 1]));
        $this->assertSame($body, $client->post('', ['a' => 2]));

        $history = $getHistory();
        $this->assertCount(2, $history);

        // Ensure both requests include api_key in their query
        foreach ($history as $transaction) {
            $req = $transaction['request'];
            parse_str($req->getUri()->getQuery(), $q);
            $this->assertArrayHasKey('api_key', $q);
            $this->assertSame('test_api_key', $q['api_key']);
        }
    }

    public function testBaseUriInteraction()
    {
        $body = ['ok' => true];
        [$client, $getHistory] = $this->makeClientWithHistoryPair([
            new Response(200, [], json_encode($body)),
        ]);

        $this->assertSame($body, $client->post('some/endpoint', ['a' => 1]));
        $history = $getHistory();
        $this->assertCount(1, $history);
        $req = $history[0]['request'];

        // The client's Guzzle base_uri should prefix the path; confirm path contains base segment
        $this->assertStringContainsString('/api/v1/', (string) $req->getUri()->getPath());
        parse_str($req->getUri()->getQuery(), $q);
        $this->assertArrayHasKey('api_key', $q);
        $this->assertSame('test_api_key', $q['api_key']);
    }

    public function testPostPutDeleteIncludeApiKeyInQuery()
    {
        // Prepare responses for POST, PUT, DELETE
        [$client, $getHistory] = $this->makeClientWithHistoryPair([
            new Response(200, [], json_encode(['ok' => true])),
            new Response(200, [], json_encode(['ok' => true])),
            new Response(200, [], json_encode(['ok' => true])),
        ]);

        // POST
        $client->post('test/endpoint/', ['foo' => 'bar']);
        // PUT
        $client->put('test/endpoint/', ['id' => 1]);
        // DELETE
        $client->delete('test/endpoint/', ['id' => 1]);

        $history = $getHistory();
        $this->assertCount(3, $history);

        foreach ($history as $entry) {
            $req = $entry['request'];
            parse_str($req->getUri()->getQuery(), $q);
            $this->assertArrayHasKey('api_key', $q);
            $this->assertSame('test_api_key', $q['api_key']);
        }
    }
}
