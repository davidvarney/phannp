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
}
