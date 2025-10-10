<?php

namespace Phannp\Tests\Resources;

use Phannp\Tests\TestCase;
use GuzzleHttp\Psr7\Response;

class RecipientsDetailedErrorsTest extends TestCase
{
    public function testCreateErrorContainsFieldErrors()
    {
        $body = [
            'error' => 'Validation failed',
            'code' => 422,
            'errors' => [
                'email' => 'invalid',
                'firstname' => 'required',
            ],
        ];

        $client = $this->makeClient([new Response(422, [], json_encode($body))]);

        $ex = $this->callAndCatchApiException(function () use ($client) {
            $client->recipients->create('Jane', 'Doe', '2 Road', '', '', 'City', 'AB12 3CD', 'GB', 'jane@example.com', null, null, 0, null, null);
        });

        $this->assertApiErrorStatusAndBody($ex, 422);
        $this->assertApiErrorJsonHasPath($ex, ['errors', 'email'], 'invalid');
        $this->assertApiErrorJsonHasPath($ex, ['errors', 'firstname'], 'required');
    }
}
