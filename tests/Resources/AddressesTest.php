<?php

namespace Phannp\Tests\Resources;

use Phannp\Tests\TestCase;
use GuzzleHttp\Psr7\Response;

class AddressesTest extends TestCase
{
    public function testValidate()
    {
        $body = ['ok' => true];
        $client = $this->makeClient([new Response(200, [], json_encode($body)), new Response(200, [], json_encode($body))]);

        $this->assertSame($body, $client->addresses->validate(['address1' => '1']));
    }

    public function testValidateThrowsOnUnknownParameter()
    {
        $this->expectException(\Phannp\Exceptions\PhannpException::class);

        $client = $this->makeClient();
        $client->addresses->validate(['unknown' => 'x']);
    }

    public function testValidateThrowsOnNonString()
    {
        $this->expectException(\Phannp\Exceptions\PhannpException::class);

        $client = $this->makeClient();
        $client->addresses->validate(['address1' => 123]);
    }

    public function testValidateRejectsInvalidState()
    {
        $this->expectException(\Phannp\Exceptions\PhannpException::class);

        $client = $this->makeClient();
        $client->addresses->validate(['address1' => '1', 'state' => 'NYC']);
    }

    public function testValidateRejectsInvalidCountry()
    {
        $this->expectException(\Phannp\Exceptions\PhannpException::class);

        $client = $this->makeClient();
        $client->addresses->validate(['address1' => '1', 'country' => 'USA']);
    }

    public function testValidateNormalizesCountryAndState()
    {
        $body = ['ok' => true];
        $client = $this->makeClient([new Response(200, [], json_encode($body))]);

        // lower-case inputs should be uppercased before sending; Mock returns body regardless
        $this->assertSame($body, $client->addresses->validate(['address1' => '1', 'state' => 'ny', 'country' => 'gb']));
    }

    public function testValidateAcceptsKnownCountryAndState()
    {
        $body = ['ok' => true];
        $client = $this->makeClient([new Response(200, [], json_encode($body))]);

        // US requires two-letter state; this should pass validation and be normalized
        $this->assertSame($body, $client->addresses->validate(['address1' => '1', 'state' => 'ny', 'country' => 'us']));
    }

    public function testValidateSendsUppercasedCountryAndState()
    {
        $body = ['ok' => true];
        [$client, $getHistory] = $this->makeClientWithHistoryPair([new Response(200, [], json_encode($body))]);

        $this->assertSame($body, $client->addresses->validate(['address1' => '1', 'state' => 'ny', 'country' => 'gb']));

        // One request should have been recorded
        $history = $getHistory();
        $this->assertCount(1, $history);

        $transaction = $history[0];
        $request = $transaction['request'];

        // The Client uses form_params for POSTs; Guzzle serializes as application/x-www-form-urlencoded
        $bodyString = (string) $request->getBody();

    // Confirm that country=GB and state=NY are present in the sent body
    $this->assertStringContainsString('country=GB', $bodyString);
    $this->assertStringContainsString('state=NY', $bodyString);

    // API key should be present in the outgoing form payload (auth[0] encoded)
    $this->assertStringContainsString('auth%5B0%5D=test_api_key', $bodyString);
    }

    public function testValidateRejectsUnknownCountry()
    {
        $this->expectException(\Phannp\Exceptions\PhannpException::class);
        $this->expectExceptionMessage('use "US" or "GB"');

        $client = $this->makeClient();
        $client->addresses->validate(['address1' => '1', 'country' => 'XX']);
    }
}
