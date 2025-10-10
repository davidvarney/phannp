<?php

namespace Phannp\Tests\Resources;

use Phannp\Tests\TestCase;
use GuzzleHttp\Psr7\Response;

class ApiErrorsTest extends TestCase
{
    public function testApiErrorPropagatesMessageAndCodeFromJson()
    {
        $body = ['message' => 'Invalid input', 'code' => 422];
        $client = $this->makeClient([new Response(422, [], json_encode($body))]);

        try {
            // Use valid dates so the request is sent and the mocked 422 is returned
            $client->reporting->summary('2025-01-01', '2025-01-31');
            $this->fail('Expected ApiException');
        } catch (\Phannp\Exceptions\ApiException $e) {
            // HTTP status should be preserved on the ApiException
            $this->assertSame(422, $e->getStatusCode());

            // Response JSON should be decodable and include the message/code
            $json = $e->getResponseJson();
            $this->assertIsArray($json);
            $this->assertArrayHasKey('message', $json);
            $this->assertSame('Invalid input', $json['message']);
            $this->assertArrayHasKey('code', $json);
            $this->assertSame(422, $json['code']);
        }
    }

    public function testApiErrorWithPlainTextBodyProvidesStatusAndBody()
    {
        $client = $this->makeClient([new Response(500, [], 'Server exploded')]);

        $this->expectException(\Phannp\Exceptions\ApiException::class);

        try {
            $client->reporting->summary('2025-01-01', '2025-01-31');
        } catch (\Phannp\Exceptions\ApiException $e) {
            $this->assertSame(500, $e->getStatusCode());
            $this->assertSame('Server exploded', $e->getResponseBody());
            $this->assertNull($e->getResponseJson());
            throw $e;
        }
    }
}
