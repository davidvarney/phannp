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

    public function testMultipartUploadSendsFileContents()
    {
        $body = ['ok' => true];

        // create a temp file with extension and known contents
        $tmp = tempnam(sys_get_temp_dir(), 'phannp_test_') . '.txt';
        file_put_contents($tmp, "hello-multipart");

        [$client, $getHistory] = $this->makeClientWithHistoryPair([
            new Response(200, [], json_encode($body)),
        ]);

        try {
            $this->assertSame($body, $client->files->upload($tmp));

            $history = $getHistory();
            $this->assertCount(1, $history);
            $request = $history[0]['request'];

            // Ensure the request used multipart/form-data
            $contentType = $request->getHeaderLine('Content-Type');
            $this->assertStringContainsString('multipart/form-data', $contentType);

            $parts = $this->parseMultipartBody($request);

            // Find the 'file' part and ensure it contains our contents
            $found = false;
            foreach ($parts as $p) {
                if ($p['name'] === 'file') {
                    $found = true;
                    $this->assertStringContainsString('hello-multipart', $p['body']);
                    // the parser exposes filename when present
                    $this->assertNotNull($p['filename']);
                    $this->assertSame(basename($tmp), $p['filename']);
                    // content type should be present and include text/plain
                    $this->assertNotNull($p['content_type']);
                    $this->assertStringContainsString('text/plain', strtolower($p['content_type']));
                }
            }

            $this->assertTrue($found, 'Expected multipart part named "file" not found');
        } finally {
            // cleanup
            @unlink($tmp);
        }
    }

    public function testMultipartUploadWithResource()
    {
        $body = ['ok' => true];

        $tmp = tempnam(sys_get_temp_dir(), 'phannp_test_') . '.txt';
        file_put_contents($tmp, "resource-contents");

        $f = fopen($tmp, 'r');

        [$client, $getHistory] = $this->makeClientWithHistoryPair([
            new Response(200, [], json_encode($body)),
        ]);

        try {
            $this->assertSame($body, $client->files->upload($f));

            $history = $getHistory();
            $this->assertCount(1, $history);
            $request = $history[0]['request'];

            $this->assertStringContainsString('multipart/form-data', $request->getHeaderLine('Content-Type'));

            $parts = $this->parseMultipartBody($request);

            $found = false;
            foreach ($parts as $p) {
                if ($p['name'] === 'file') {
                    $found = true;
                    $this->assertStringContainsString('resource-contents', $p['body']);
                    // allow either null filename or basename
                    if ($p['filename'] !== null) {
                        $this->assertSame(basename($tmp), $p['filename']);
                    }
                    $this->assertNotNull($p['content_type']);
                }
            }

            $this->assertTrue($found, 'Expected multipart part named "file" not found');
        } finally {
            @fclose($f);
            @unlink($tmp);
        }
    }
}
