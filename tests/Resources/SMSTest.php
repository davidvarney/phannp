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

        $this->assertSame($body, $client->sms->send(['to' => '447777']));
        $this->assertSame($body, $client->sms->get(1));
        $this->assertSame($body, $client->sms->list());
    }
}
