<?php

namespace Phannp\Tests\Resources;

use Phannp\Tests\TestCase;
use GuzzleHttp\Psr7\Response;

class FilesDetailedErrorsTest extends TestCase
{
    public function testUploadErrorWithFieldDetails()
    {
        $body = [
            'error' => 'Validation failed',
            'code' => 422,
            'details' => [
                'file' => ['size' => 'too_large', 'type' => 'unsupported'],
            ],
        ];

        $client = $this->makeClient([new Response(422, [], json_encode($body))]);

        $ex = $this->callAndCatchApiException(function () use ($client) {
            $client->files->upload('/tmp/x');
        });

        $this->assertSame(422, $ex->getStatusCode());
        $json = $ex->getResponseJson();
        $this->assertIsArray($json);
        $this->assertArrayHasKey('details', $json);
        $this->assertArrayHasKey('file', $json['details']);
        $this->assertSame('too_large', $json['details']['file']['size']);
        $this->assertSame('unsupported', $json['details']['file']['type']);
    }
}
