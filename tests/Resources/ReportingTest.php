<?php

namespace Phannp\Tests\Resources;

use Phannp\Tests\TestCase;
use GuzzleHttp\Psr7\Response;

class ReportingTest extends TestCase
{
    public function testGetStatsAndCampaignStats()
    {
        $body = ['ok' => true];
        $client = $this->makeClient([new Response(200, [], json_encode($body)), new Response(200, [], json_encode($body))]);

        $this->assertSame($body, $client->reporting->getStats());
        $this->assertSame($body, $client->reporting->getCampaignStats(1));
    }
}
