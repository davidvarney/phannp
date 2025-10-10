<?php

namespace Phannp\Tests\Resources;

use Phannp\Tests\TestCase;
use GuzzleHttp\Psr7\Response;

class SMSTest extends TestCase
{
    public function testSendGetList()
    {
        $body = ['ok' => true];
        [$client, $getHistory] = $this->makeClientWithHistoryPair([
            new Response(200, [], json_encode($body)),
        ]);

        // Assert create() sends normalized phone in the request
        $this->assertSame($body, $client->sms->create('Test message', true, '+44 (7911) 123456', null, 'GB'));
        $history = $getHistory();
        $this->assertCount(1, $history);
        $req = $history[0]['request'];
        $this->assertSame('POST', $req->getMethod());
        $params = $this->getFormParams($req);
        $this->assertSame('+447911123456', $params['phone_number']);
        $this->assertSame('GB', $params['country']);
    }

    public function testCreateRequiresPhoneOrRecipient()
    {
        $this->expectException(\InvalidArgumentException::class);
        $client = $this->makeClient();
        $client->sms->create('Hello', false, null, null, null);
    }

    public function testCreateRejectsInvalidPhone()
    {
        $this->expectException(\InvalidArgumentException::class);
        $client = $this->makeClient();
        $client->sms->create('Hello', false, '+44 79-11abc3456', null, null);
    }

    public function testCreateWithRecipientIdOmitsPhone()
    {
        $body = ['ok' => true];
        [$client, $getHistory] = $this->makeClientWithHistoryPair([new Response(200, [], json_encode($body))]);

        $this->assertSame($body, $client->sms->create('Hello', false, null, 123, null));
        $history = $getHistory();
        $this->assertCount(1, $history);
        $req = $history[0]['request'];
        $params = $this->getFormParams($req);
        $this->assertArrayNotHasKey('phone_number', $params);
        $this->assertSame('123', $params['recipient_id']);
    }
}
