<?php

namespace Phannp\Tests;

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\Response;
use Phannp\Client;
use Psr\Http\Message\RequestInterface;

class TestCase extends \PHPUnit\Framework\TestCase
{
    protected function makeClient(array $responses = [], array $options = []): Client
    {
        $mock = new MockHandler($responses ?: [new Response(200, [], json_encode(['ok' => true]))]);
        $handler = HandlerStack::create($mock);

        $guzzleOptions = array_merge(['handler' => $handler], $options);

        return new Client('test_api_key', $guzzleOptions);
    }

    /**
     * Create a Client with a Guzzle history middleware so tests may inspect
     * the outgoing requests. Returns an array with the client and a reference
     * to the history array: [Client, array & $history]
     *
     * @param array $responses
     * @param array $options
     * @return array [Client, array&]
     */
    protected function makeClientWithHistory(array $responses = [], array $options = [], array & $history = []): Client
    {
        $mock = new MockHandler($responses ?: [new Response(200, [], json_encode(['ok' => true]))]);
        $history = [];
        $historyMiddleware = \GuzzleHttp\Middleware::history($history);

        $handler = HandlerStack::create($mock);
        $handler->push($historyMiddleware);

        $guzzleOptions = array_merge(['handler' => $handler], $options);

        return new Client('test_api_key', $guzzleOptions);
    }

    /**
     * Ergonomic wrapper that returns [Client, History] so tests don't need to
     * pre-declare a history variable.
     *
     * @param array $responses
     * @param array $options
     * @return array [Client, array]
     */
    protected function makeClientWithHistoryPair(array $responses = [], array $options = []): array
    {
        $history = [];
        $client = $this->makeClientWithHistory($responses, $options, $history);

        // Return a getter closure so the caller can obtain the up-to-date history
        $getHistory = function () use (&$history) {
            return $history;
        };

        return [$client, $getHistory];
    }

    /**
     * Convenience: return parsed form params for a request (for form_params bodies).
     * @param RequestInterface $request
     * @return array<string,mixed>
     */
    protected function getFormParams(RequestInterface $request): array
    {
        $body = (string) $request->getBody();
        parse_str($body, $params);
        return $params;
    }

    /**
     * Return a map of multipart part name => part array (as returned by parseMultipartBody)
     * @param RequestInterface $request
     * @return array<string,array>
     */
    protected function getMultipartMap(RequestInterface $request): array
    {
        $parts = $this->parseMultipartBody($request);
        $map = [];
        foreach ($parts as $p) {
            $map[$p['name']] = $p;
        }
        return $map;
    }

    /**
     * Convenience wrapper that returns [Client, History] and accepts a list of responses.
     * Kept for readability in tests that need many responses.
     * @param array $responses
     * @param array $options
     * @return array [Client, callable]
     */
    protected function makeClientWithHistoryAndResponses(array $responses, array $options = []): array
    {
        return $this->makeClientWithHistoryPair($responses, $options);
    }

    /**
     * Parse a multipart/form-data request body into an array of parts.
     * Returns an array of parts where each part is an associative array with
     * keys: name, headers (assoc), and body (string).
     *
     * This is a lightweight parser intended for test assertions only and
     * does not fully implement RFC2046 — it's enough to extract part names
     * and bodies for the SDK tests.
     *
     * @param RequestInterface $request
     * @return array<int,array{name:string,headers:array<string,string>,body:string}>
     */
    protected function parseMultipartBody(RequestInterface $request): array
    {
        $contentType = $request->getHeaderLine('Content-Type');
        if (!preg_match('/boundary=(?<b>[^;]+)/', $contentType, $m)) {
            return [];
        }

        $boundary = '--' . trim($m['b'], '"');
        $raw = (string) $request->getBody();

        $sections = preg_split('/(?:\r\n)?' . preg_quote($boundary, '/') . '(?:--)?(?:\r\n)?/', $raw);
        $parts = [];

        foreach ($sections as $section) {
            $section = trim($section, "\r\n");
            if ($section === '' || $section === '--') {
                continue;
            }

            // Split headers and body
            $partsSplit = preg_split("/\r\n\r\n/", $section, 2);
            if (count($partsSplit) !== 2) {
                continue;
            }

            [$rawHeaders, $body] = $partsSplit;
            $headerLines = preg_split('/\r\n/', $rawHeaders);
            $headers = [];
            $name = '';

            foreach ($headerLines as $line) {
                $line = trim($line);
                if ($line === '') {
                    continue;
                }
                [$hName, $hVal] = array_map('trim', explode(':', $line, 2) + [1 => '']);
                $headers[$hName] = $hVal;

                if (stripos($hName, 'Content-Disposition') === 0) {
                    // Parse Content-Disposition params into a map
                    $dispMap = [];
                    if (preg_match_all('/(?<k>\w+)="(?<v>[^"]+)"/', $hVal, $dm, PREG_SET_ORDER)) {
                        foreach ($dm as $entry) {
                            $dispMap[$entry['k']] = $entry['v'];
                        }
                    }
                    if (isset($dispMap['name'])) {
                        $name = $dispMap['name'];
                    }
                    // expose parsed disposition map under a dedicated header key
                    if (!empty($dispMap)) {
                        $headers['__disposition'] = $dispMap;
                    }
                }
                if (stripos($hName, 'Content-Type') === 0) {
                    // expose parsed content-type value (e.g. text/plain)
                    $headers['__content_type'] = $hVal;
                }
            }

            // Build a structured disposition map and filename for convenience
            $disposition = $headers['__disposition'] ?? null;
            $filename = $disposition['filename'] ?? null;

            $parts[] = [
                'name' => $name,
                'filename' => $filename,
                'disposition' => $disposition,
                'content_type' => $headers['__content_type'] ?? null,
                'headers' => $headers,
                'body' => $body,
            ];
        }

        return $parts;
    }

    /**
     * Convenience wrapper: given a request, return an associative map of part
     * name => body string for quick assertions.
     *
     * @param RequestInterface $request
     * @return array<string,string>
     */
    protected function getMultipartParts(RequestInterface $request): array
    {
        $parts = $this->parseMultipartBody($request);
        $map = [];
        foreach ($parts as $p) {
            $map[$p['name']] = $p['body'];
        }
        return $map;
    }

    /**
     * Execute a callable that is expected to throw an ApiException and return
     * the caught exception for further assertions.
     *
     * @param callable $fn A zero-argument callable which will perform the API call.
     * @return \Phannp\Exceptions\ApiException
     */
    protected function callAndCatchApiException(callable $fn): \Phannp\Exceptions\ApiException
    {
        try {
            $fn();
            $this->fail('Expected ApiException');
        } catch (\Phannp\Exceptions\ApiException $e) {
            return $e;
        }
    }

    /**
     * Assert the ApiException has the expected HTTP status and optional raw body.
     *
     * @param \Phannp\Exceptions\ApiException $ex
     * @param int $status
     * @param string|null $body
     * @return void
     */
    protected function assertApiErrorStatusAndBody(\Phannp\Exceptions\ApiException $ex, int $status, ?string $body = null): void
    {
        $this->assertSame($status, $ex->getStatusCode());
        if ($body !== null) {
            $this->assertSame($body, $ex->getResponseBody());
        }
    }

    /**
     * Assert the ApiException JSON response contains a nested value specified by $path.
     * Example: $path = ['details','file','size'] will assert $json['details']['file']['size'] === $expected
     *
     * @param \Phannp\Exceptions\ApiException $ex
     * @param array $path
     * @param mixed $expected
     * @return void
     */
    protected function assertApiErrorJsonHasPath(\Phannp\Exceptions\ApiException $ex, array $path, $expected): void
    {
        $json = $ex->getResponseJson();
        $this->assertIsArray($json, 'Expected response JSON to be an array');

        $cursor = $json;
        foreach ($path as $key) {
            $this->assertIsArray($cursor, 'Intermediate value is not an array for path element: ' . (string)$key);
            $this->assertArrayHasKey($key, $cursor, 'Missing expected JSON key: ' . (string)$key);
            $cursor = $cursor[$key];
        }

        $this->assertSame($expected, $cursor);
    }
}
