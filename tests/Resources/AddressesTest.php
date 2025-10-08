<?php

namespace Phannp\Tests\Resources;

use Phannp\Tests\TestCase;
use GuzzleHttp\Psr7\Response;

class AddressesTest extends TestCase
{
    public function testValidateAndAutocomplete()
    {
        $body = ['ok' => true];
        $client = $this->makeClient([new Response(200, [], json_encode($body)), new Response(200, [], json_encode($body))]);

        $this->assertSame($body, $client->addresses->validate(['address' => '1']));
        $this->assertSame($body, $client->addresses->autocomplete('SW1A1AA'));
    }
}
