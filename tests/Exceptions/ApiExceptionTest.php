<?php

namespace Phannp\Tests\Exceptions;

use Phannp\Exceptions\ApiException;
use PHPUnit\Framework\TestCase;
use GuzzleHttp\Psr7\Utils;
use GuzzleHttp\Psr7\Response;

class ApiExceptionTest extends TestCase
{
    public function testGetResponseJsonReturnsArrayForValidJson()
    {
        $body = json_encode(['error' => 'bad', 'code' => 123]);
        $response = new Response(400, [], $body);

        $ex = ApiException::fromResponse('msg', null, $response);

        $this->assertIsArray($ex->getResponseJson());
        $this->assertSame('bad', $ex->getResponseJson()['error']);
        $this->assertSame(123, $ex->getResponseJson()['code']);
    }

    public function testGetResponseJsonReturnsNullForNonJson()
    {
        $response = new Response(500, [], 'Server error');

        $ex = ApiException::fromResponse('msg', null, $response);

        $this->assertNull($ex->getResponseJson());
    }

    public function testGetResponseJsonReturnsNullForNullResponse()
    {
        $ex = ApiException::fromResponse('msg', null, null);

        $this->assertNull($ex->getResponseJson());
    }

    public function testGetStatusCodeAndBodyForJsonResponse()
    {
        $body = json_encode(['error' => 'bad']);
        $response = new Response(400, [], $body);

        $ex = ApiException::fromResponse('msg', null, $response);

        $this->assertSame(400, $ex->getStatusCode());
        $this->assertSame($body, $ex->getResponseBody());
    }

    public function testGetStatusCodeAndBodyForNonJsonResponse()
    {
        $response = new Response(500, [], 'Server error');

        $ex = ApiException::fromResponse('msg', null, $response);

        $this->assertSame(500, $ex->getStatusCode());
        $this->assertSame('Server error', $ex->getResponseBody());
    }

    public function testGetStatusCodeAndBodyForNullResponse()
    {
        $ex = ApiException::fromResponse('msg', null, null);

        $this->assertNull($ex->getStatusCode());
        $this->assertNull($ex->getResponseBody());
    }
}
