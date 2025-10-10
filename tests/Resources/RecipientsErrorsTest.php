<?php

namespace Phannp\Tests\Resources;

use Phannp\Tests\TestCase;
use GuzzleHttp\Psr7\Response;

class RecipientsErrorsTest extends TestCase
{
    public function testCreateServerSideValidationErrorPropagates()
    {
        $body = ['errors' => ['firstname' => 'required', 'email' => 'invalid'], 'code' => 422];
        $client = $this->makeClient([new Response(422, [], json_encode($body))]);

        try {
            // Provide valid-looking client-side values to ensure request is sent
            $client->recipients->create('John', 'Doe', '1 Main St', '', '', 'Town', '12345', 'GB', 'john@example.com', null, null, 0, null, null);
            $this->fail('Expected ApiException');
        } catch (\Phannp\Exceptions\ApiException $e) {
            $this->assertSame(422, $e->getStatusCode());
            $json = $e->getResponseJson();
            $this->assertIsArray($json);
            $this->assertArrayHasKey('errors', $json);
            $this->assertArrayHasKey('firstname', $json['errors']);
            $this->assertSame('required', $json['errors']['firstname']);
            $this->assertSame(422, $json['code']);
        }
    }
}
