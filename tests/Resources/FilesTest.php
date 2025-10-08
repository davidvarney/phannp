<?php

namespace Phannp\Tests\Resources;

use Phannp\Tests\TestCase;
use GuzzleHttp\Psr7\Response;

class FilesTest extends TestCase
{
    public function testUploadGetListDelete()
    {
        $body = ['ok' => true];
        $client = $this->makeClient([
            new Response(200, [], json_encode($body)),
            new Response(200, [], json_encode($body)),
            new Response(200, [], json_encode($body)),
            new Response(200, [], json_encode($body)),
        ]);

        $this->assertSame($body, $client->files->upload(['file' => 'x']));
        $this->assertSame($body, $client->files->get(1));
        $this->assertSame($body, $client->files->list());
        $this->assertSame($body, $client->files->delete(1));
    }
}
