<?php

namespace Phannp\Tests\Resources;

use Phannp\Tests\TestCase;
use GuzzleHttp\Psr7\Response;
use Phannp\Exceptions\ApiException;

class FilesTest extends TestCase
{
    public function testUploadCreateList()
    {
        $body = ['ok' => true];
        $client = $this->makeClient([
            new Response(200, [], json_encode($body)),
            new Response(200, [], json_encode($body)),
            new Response(200, [], json_encode($body)),
        ]);

        // upload accepts a file path string
        $this->assertSame($body, $client->files->upload('/tmp/x'));

        // folder operations: createFolder and listFolders
        $this->assertSame($body, $client->files->createFolder('New Folder'));
        $this->assertSame($body, $client->files->listFolders());
    }

    public function testUploadWithFolderId()
    {
        $body = ['ok' => true];
        [$client, $getHistory] = $this->makeClientWithHistoryPair([
            new Response(200, [], json_encode($body)),
        ]);

        $this->assertSame($body, $client->files->upload('/tmp/x', 42));

        $history = $getHistory();
        $this->assertCount(1, $history);
        $request = $history[0]['request'];

        // POST should use form_params when no file exists on disk in tests
        $this->assertSame('POST', $request->getMethod());

        $bodyStr = (string) $request->getBody();
        // Ensure our folder_id is present in the request body (form-encoded)
        $this->assertStringContainsString('folder_id', $bodyStr);
        $this->assertStringContainsString('42', $bodyStr);
    }

    public function testUploadApiError()
    {
        $errorBody = ['error' => 'server failure'];
        $client = $this->makeClient([
            new Response(500, [], json_encode($errorBody)),
        ]);

        $this->expectException(ApiException::class);

        try {
            $client->files->upload('/tmp/x');
        } catch (ApiException $e) {
            $this->assertSame(500, $e->getStatusCode());
            $this->assertSame(json_encode($errorBody), $e->getResponseBody());
            throw $e;
        }
    }
}
