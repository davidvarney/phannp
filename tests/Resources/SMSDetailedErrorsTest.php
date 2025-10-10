<?php

namespace Phannp\Tests\Resources;

use Phannp\Tests\TestCase;
use GuzzleHttp\Psr7\Response;

class SMSDetailedErrorsTest extends TestCase
{
    public function testCreateErrorContainsRecipientIdDetail()
    {
        $body = [
            'error' => 'Invalid recipient_id',
            'code' => 422,
            'details' => [
                'recipient_id' => 'not_found',
            ],
        ];

        $client = $this->makeClient([new Response(422, [], json_encode($body))]);

        $ex = $this->callAndCatchApiException(function () use ($client) {
            // call using recipientId to ensure phone validation is bypassed
            $client->sms->create('Hello', false, null, 99999, null);
        });

        $this->assertApiErrorStatusAndBody($ex, 422);
        $this->assertApiErrorJsonHasPath($ex, ['details', 'recipient_id'], 'not_found');
    }
}
