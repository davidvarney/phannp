<?php

namespace Phannp\Tests\Resources;

use Phannp\Tests\TestCase;
use GuzzleHttp\Psr7\Response;

class FilesErrorsTest extends TestCase
{
    public function testUploadApiErrorProvidesJson()
    {
        $body = ['error' => 'Upload failed', 'code' => 400];
        $client = $this->makeClient([new Response(400, [], json_encode($body))]);

        try {
            $client->files->upload('/tmp/x');
            $this->fail('Expected ApiException');
        } catch (\Phannp\Exceptions\ApiException $e) {
            $this->assertSame(400, $e->getStatusCode());
            $json = $e->getResponseJson();
            $this->assertIsArray($json);
            $this->assertSame('Upload failed', $json['error']);
            $this->assertSame(400, $json['code']);
        }
    }

    public function testUploadApiErrorPlainText()
    {
        $client = $this->makeClient([new Response(503, [], 'Service unavailable')]);

        $this->expectException(\Phannp\Exceptions\ApiException::class);

        try {
            $client->files->upload('/tmp/x');
        } catch (\Phannp\Exceptions\ApiException $e) {
            $this->assertSame(503, $e->getStatusCode());
            $this->assertSame('Service unavailable', $e->getResponseBody());
            $this->assertNull($e->getResponseJson());
            throw $e;
        }
    }
}
