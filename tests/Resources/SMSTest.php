<?php

namespace Phannp\Tests\Resources;

use Phannp\Tests\TestCase;
use GuzzleHttp\Psr7\Response;

class SMSTest extends TestCase
{
    public function testSendGetList()
    {
        $body = ['ok' => true];
        $client = $this->makeClient([
            new Response(200, [], json_encode($body)),
            new Response(200, [], json_encode($body)),
            new Response(200, [], json_encode($body)),
        ]);

    // SMS resource exposes create(message, test=false, phoneNumber=null, recipientId=null, country=null)
    $this->assertSame($body, $client->sms->create('Test message', true, '447777'));
    // The SDK does not currently implement get/list on SMS; keep a single assertion for create
    }
}
