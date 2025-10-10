<?php

namespace Phannp\Tests\Resources;

use Phannp\Tests\TestCase;
use GuzzleHttp\Psr7\Response;

class SMSErrorsTest extends TestCase
{
    public function testCreateApiErrorPropagatesJson()
    {
        $body = ['error' => 'Invalid recipient', 'code' => 422];
        $client = $this->makeClient([new Response(422, [], json_encode($body))]);

        try {
            // Provide a valid E.164 phone so client-side validation passes
            $client->sms->create('Hello', false, '+447911123456');
            $this->fail('Expected ApiException');
        } catch (\Phannp\Exceptions\ApiException $e) {
            $this->assertSame(422, $e->getStatusCode());
            $json = $e->getResponseJson();
            $this->assertIsArray($json);
            $this->assertSame('Invalid recipient', $json['error']);
            $this->assertSame(422, $json['code']);
        }
    }

    public function testCreateApiErrorPlainText()
    {
        $client = $this->makeClient([new Response(502, [], 'Bad gateway')]);

        $this->expectException(\Phannp\Exceptions\ApiException::class);

        try {
            $client->sms->create('Hi', false, '+447911123456');
        } catch (\Phannp\Exceptions\ApiException $e) {
            $this->assertSame(502, $e->getStatusCode());
            $this->assertSame('Bad gateway', $e->getResponseBody());
            $this->assertNull($e->getResponseJson());
            throw $e;
        }
    }
}
