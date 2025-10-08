<?php

namespace Phannp\Tests\Resources;

use Phannp\Tests\TestCase;
use GuzzleHttp\Psr7\Response;

class AccountTest extends TestCase
{
    public function testGetAndBalance()
    {
        $body = ['account' => ['id' => 1]];
        $client = $this->makeClient([new Response(200, [], json_encode($body)), new Response(200, [], json_encode($body))]);

        $this->assertSame($body, $client->account->get());
        $this->assertSame($body, $client->account->getBalance());
    }
}
