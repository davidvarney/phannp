<?php

namespace Phannp\Tests\Utilities;

use Phannp\Tests\TestCase;
use GuzzleHttp\Psr7\Request;

class MultipartParserTest extends TestCase
{
    public function testParseMultipartBody()
    {
        $boundary = '----WebKitFormBoundary7MA4YWxkTrZu0gW';
        $body = "--{$boundary}\r\n"
            . "Content-Disposition: form-data; name=\"field1\"\r\n\r\n"
            . "value1\r\n"
            . "--{$boundary}\r\n"
            . "Content-Disposition: form-data; name=\"file\"; filename=\"a.txt\"\r\n"
            . "Content-Type: text/plain\r\n\r\n"
            . "filecontents\r\n"
            . "--{$boundary}--\r\n";

        $headers = ['Content-Type' => "multipart/form-data; boundary={$boundary}"]; 
        $request = new Request('POST', '/upload', $headers, $body);

        $parts = $this->parseMultipartBody($request);

        $this->assertCount(2, $parts);
        $this->assertSame('field1', $parts[0]['name']);
        $this->assertSame('value1', $parts[0]['body']);
    $this->assertSame('file', $parts[1]['name']);
    $this->assertStringContainsString('filecontents', $parts[1]['body']);
    $this->assertSame('a.txt', $parts[1]['filename']);
    $this->assertIsArray($parts[1]['disposition']);
    $this->assertSame('a.txt', $parts[1]['disposition']['filename']);
    $this->assertSame('text/plain', $parts[1]['content_type']);
    }

    public function testGetMultipartPartsMap()
    {
        $boundary = 'MyBoundary';
        $body = "--{$boundary}\r\n"
            . "Content-Disposition: form-data; name=\"a\"\r\n\r\n"
            . "one\r\n"
            . "--{$boundary}\r\n"
            . "Content-Disposition: form-data; name=\"b\"\r\n\r\n"
            . "two\r\n"
            . "--{$boundary}--\r\n";

        $headers = ['Content-Type' => "multipart/form-data; boundary={$boundary}"];
        $request = new Request('POST', '/x', $headers, $body);

        $map = $this->getMultipartParts($request);

        $this->assertArrayHasKey('a', $map);
        $this->assertArrayHasKey('b', $map);
        $this->assertSame('one', $map['a']);
        $this->assertSame('two', $map['b']);
    }
}
