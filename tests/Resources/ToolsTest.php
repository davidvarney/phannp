<?php

namespace Phannp\Tests\Resources;

use Phannp\Tests\TestCase;
use GuzzleHttp\Psr7\Response;

class ToolsTest extends TestCase
{
    public function testHelpers()
    {
        $body = ['ok' => true];
        $client = $this->makeClient([
            new Response(200, [], json_encode($body)),
            new Response(200, [], json_encode($body)),
            new Response(200, [], json_encode($body)),
        ]);

        $this->assertSame($body, $client->tools->getCountries());
        $this->assertSame($body, $client->tools->getRegions('GB'));
        $this->assertSame($body, $client->tools->getPricing());
    }
}
