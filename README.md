# Phannp

A modern PHP SDK for the Stannp Direct Mail API.

## Installation

Install via Composer:

```bash
composer require davidvarney/phannp
```

## Requirements

- PHP 7.4 or higher
- Guzzle HTTP client 7.0 or higher

## Usage

### Initialize the Client

```php
<?php

require 'vendor/autoload.php';

use Phannp\Client;

$client = new Client('your-api-key-here');
```

### Postcards

```php
// Create a postcard
// Note: the SDK's Postcards resource currently expects a size string
// (e.g. '4x6', '6x9') and will accept additional params in future.
$postcard = $client->postcards->create('4x6');

// Get a postcard
$postcard = $client->postcards->get(789);

// Cancel a postcard
$result = $client->postcards->cancel(789);
```

Postcards::create parameters

- size (string) — postcard size, e.g. '4x6', '6x9', '6x11'.
- (future) recipient/template/file — the SDK may accept additional params; currently use `create('size')`.

Example using a template (future-proof):

```php
// If you have a template id and recipient id available, the create call
// will eventually accept a combined payload. For now you can pass a size
// and send the template/recipient via your campaign or direct create call
// when the SDK supports it.
$postcard = $client->postcards->create('4x6');
```

### Letters

```php
// Create a letter
// The Letters resource supports both creating a merged letter (pass a
// recipient id or array) or posting a pre-merged PDF using post().
// Example: create with a recipient array (mail-merge)
$letter = $client->letters->create([
    'firstname' => 'Jane',
    'lastname'  => 'Doe',
    'address1'  => '1 Example St',
    'city'      => 'London',
    'postcode'  => 'EC1A 1BB',
    'country'   => 'GB',
]);

// Or: post a pre-merged PDF (country required)
$letter = $client->letters->post('GB', false, 'https://example.com/file.pdf');

// Get a letter
$letter = $client->letters->get(789);

// Cancel a letter
$result = $client->letters->cancel(789);
```

Letters::create parameters

- recipient (mixed) — mandatory. Either an existing recipient ID (int/string) or an associative array defining a new recipient (keys like firstname, lastname, address1, city, postcode, country).
- test (bool) — optional. Generate a sample PDF without dispatch when true.
- template (mixed) — optional. Template ID to use for mail-merge.
- file (mixed) — optional. Instead of template/pages, you can pass a local file path, resource (fopen), or a URL to a PDF/DOC file.
- duplex (bool) — optional. Defaults to true; set false to print only on the front.
- clearzone (bool) — optional. Defaults to false; overlay clear zones with white when true.
- post_unverified (bool) — optional. Defaults to true; set to false to prevent posting unverified addresses.
- tags (string) — optional. Comma-separated tags.
- addons (string) — optional. Comma-separated addon codes, e.g. 'FIRST_CLASS'.

Example — create using a template id and recipient id:

```php
// Create with an existing recipient and a template id
$letter = $client->letters->create(123, false, 456);
```

Example — post a pre-merged file (resource or URL):

```php
// Pass a URL
$letter = $client->letters->post('GB', false, 'https://example.com/file.pdf');

// Or pass a local resource
$fp = fopen('/path/to/letter.pdf', 'r');
$letter = $client->letters->post('GB', false, $fp);
fclose($fp);
```

### Recipients

```php
// Create a recipient (positional parameters)
// create(firstname, lastname, address1, address2, address3, city, postcode, country,
//        email, phone_number, ref_id, group_id, on_duplicate, test_level)
$recipient = $client->recipients->create(
    'Alice', 'Smith', '23 Baker Street', 'Flat 4', '', 'London', 'NW1 6XE', 'GB',
    'alice.smith@example.co.uk', '+447911123456', 'alice-001', 0, 'update', 'standard'
);

// Get a recipient
$recipient = $client->recipients->get(123);

// List recipients
// list(groupId, offset = 0, limit = 0) — pass groupId to filter, use 0 for all groups
$recipients = $client->recipients->list(0, 0, 10);

// Delete a recipient
$result = $client->recipients->delete(123);

// Import recipients in bulk
// import(file, group_id, duplicates, no_headings, mappings)
// - file: path to CSV/XLS, a base64 CSV string, or a URL to the file
// - group_id: integer group to import into (0 = all / none)
// - duplicates: 'update' | 'ignore' | 'duplicate'
// - no_headings: true if the file has no header row
// - mappings: comma-separated mapping string when headings differ or no_headings=true
$result = $client->recipients->import(
    '/tmp/recipients.csv',
    0,
    'update',
    false,
    'firstname,lastname,company,address1,address2,city,postcode,country,email,phone'
);
```

### Groups

```php
// Create a group
$group = $client->groups->create(['name' => 'VIP Customers']);

// Get a group
$group = $client->groups->get(123);

// List groups
$groups = $client->groups->list();

// Delete a group
$result = $client->groups->delete(123);

// Add recipient to group
$result = $client->groups->addRecipient(123, 456);

// Remove recipient from group
$result = $client->groups->removeRecipient(123, 456);
```

### Events

```php
// Create an event
$event = $client->events->create(
    'recipient-123', // recipient id or external reference
    'PURCHASE',       // event name
    '49.99',          // value
    true,             // conversion
    json_encode(['sku' => 'X']), // data
    'ref-1'           // ref
);

// Optionally: list or get events if supported by your account
$events = $client->events->list(['limit' => 10]);
$event = $client->events->get(123);
```

### Files

```php
// Upload a file
$file = $client->files->upload(['file' => 'path/to/file.pdf']);

// Get a file
$file = $client->files->get(123);

// List files
$files = $client->files->list(['limit' => 10]);

// Delete a file
$result = $client->files->delete(123);
```

Multipart uploads (files)

The SDK supports uploading files either by file path, by resource (e.g. fopen),
or by URL. In tests we use Guzzle's history middleware to inspect outgoing
requests and assert multipart bodies.

Upload examples:

```php
// Upload via file path (SDK will detect and send multipart)
$file = $client->files->upload(['file' => '/path/to/file.pdf']);

// Upload via resource
$fp = fopen('/path/to/file.pdf', 'r');
$file = $client->files->upload(['file' => $fp]);
fclose($fp);

// Upload via URL (the SDK will forward the URL string)
$file = $client->files->upload(['file' => 'https://example.com/file.pdf']);
```

Additional upload examples

```php
// Upload a file as part of creating a campaign (file can be a path, resource, or URL)
$campaign = $client->campaigns->create([
    'name' => 'Spring Promo',
    'file' => '/tmp/flyer.pdf', // local path
]);

// Upload using a resource (useful when streaming files)
$fp = fopen('/tmp/flyer.pdf', 'r');
$campaign = $client->campaigns->create([
    'name' => 'Spring Promo',
    'file' => $fp,
]);
fclose($fp);

// Or pass a URL if the file is hosted remotely
$campaign = $client->campaigns->create([
    'name' => 'Spring Promo',
    'file' => 'https://cdn.example.com/flyer.pdf',
]);
```

Asserting multipart in tests

Use the test helper `makeClientWithHistoryPair()` to capture outgoing requests and
`parseMultipartBody()` / `getMultipartParts()` to inspect multipart parts.

```php
[$client, $getHistory] = $this->makeClientWithHistoryPair([
    new \GuzzleHttp\Psr7\Response(200, [], json_encode(['ok' => true])),
]);

$client->files->upload(['file' => fopen('/tmp/a.pdf', 'r')]);

$history = $getHistory();
$req = $history[0]['request'];

$this->assertStringContainsString('multipart/form-data', $req->getHeaderLine('Content-Type'));
$parts = $this->parseMultipartBody($req);
$this->assertNotEmpty($parts);

// Find the file part by name and assert filename/content
$filePart = null;
foreach ($parts as $p) {
    if ($p['name'] === 'file') {
        $filePart = $p;
        break;
    }
}

$this->assertNotNull($filePart);
$this->assertStringContainsString('%PDF', $filePart['body']);
```

Concrete test assertion example (full snippet)

```php
public function testFileUploadSendsMultipart()
{
    [$client, $getHistory] = $this->makeClientWithHistoryPair([
        new \GuzzleHttp\Psr7\Response(200, [], json_encode(['ok' => true])),
    ]);

    // The SDK accepts resources, paths or URLs
    $fp = fopen(__DIR__ . '/fixtures/sample.pdf', 'r');
    $client->files->upload(['file' => $fp]);
    fclose($fp);

    $history = $getHistory();
    $this->assertCount(1, $history);
    $req = $history[0]['request'];

    // Header should indicate multipart
    $this->assertStringContainsString('multipart/form-data', $req->getHeaderLine('Content-Type'));

    // Parse parts and assert
    $parts = $this->parseMultipartBody($req);
    $this->assertNotEmpty($parts);

    $filePart = null;
    foreach ($parts as $p) {
        if ($p['name'] === 'file') {
            $filePart = $p;
            break;
        }
    }

    $this->assertNotNull($filePart, 'File part missing in multipart body');
    $this->assertNotEmpty($filePart['filename']);
    $this->assertStringContainsString('%PDF', $filePart['body']);
}
```

### Reporting

```php
// Get stats
$stats = $client->reporting->getStats(['from' => '2023-01-01', 'to' => '2023-12-31']);

// Get campaign stats
$campaignStats = $client->reporting->getCampaignStats(123);
```

### Campaigns

```php
// Create a campaign
$campaign = $client->campaigns->create(['name' => 'Summer Sale']);

// Get a campaign
$campaign = $client->campaigns->get(123);

// List campaigns
$campaigns = $client->campaigns->list(['limit' => 10]);

// Delete a campaign
$result = $client->campaigns->delete(123);
```

### Selections

```php
// Create a selection
$selection = $client->selections->create(['name' => 'Active Customers']);

// Get a selection
$selection = $client->selections->get(123);

// List selections
$selections = $client->selections->list(['limit' => 10]);
```

### Account

```php
// Get account information
$account = $client->account->get();

// Get account balance
$balance = $client->account->getBalance();

// Top up account
$result = $client->account->topUp(['amount' => 100]);
```

Account top-up caveats
- Top-ups are forwarded directly to the Stannp API. Confirm the account `currency` and available balance with `getBalance()` before calling `topUp()` to avoid unexpected charges.
- The SDK forwards whatever `amount` you send as-is. If you need idempotency for retries, implement an external idempotency key on your side and avoid calling `topUp()` multiple times for the same logical operation.
- Rate limits and limits on top-up amounts are enforced by Stannp; the SDK will surface API errors as `Phannp\Exceptions\ApiException` with the upstream message and HTTP status code.


### Addresses

```php
// Validate an address
$result = $client->addresses->validate([
    'address1' => '123 Main St',
    'city' => 'New York',
    'postcode' => '10001',
    'country' => 'US',
]);

// Autocomplete address
$addresses = $client->addresses->autocomplete('10001');
```

Notes on Addresses validation
- The `Addresses::validate()` helper accepts only the following keys: `company`, `address1`, `address2`, `city`, `state`, `zipcode`, and `country`.
- All provided values must be strings.
- `country` must be an ISO 3166-1 alpha-2 code. For compatibility with Stannp the SDK currently accepts only `US` and `GB` and will suggest the allowed codes on error (for example: use "US" or "GB").
- `state` and `country` values are normalized to uppercase before being sent to the API (e.g. `gb` -> `GB`, `ny` -> `NY`).

If you need to assert what the SDK sends, the test helpers add a Guzzle history middleware so any outgoing requests can be inspected in tests.

Asserting outgoing requests (examples)

Example — ergonomic pair helper (recommended):

```php
[$client, $getHistory] = $this->makeClientWithHistoryPair([
    new \GuzzleHttp\Psr7\Response(200, [], json_encode(['ok' => true])),
]);

$client->campaigns->approve(123);

// Get a fresh copy of the recorded history
$history = $getHistory();
$this->assertCount(1, $history);
$entry = $history[0];
$request = $entry['request'];

// Assert request method and URI
$this->assertSame('POST', $request->getMethod());
$this->assertStringContainsString('campaigns/approve', (string) $request->getUri());

// Assert headers
$this->assertStringContainsString('application/x-www-form-urlencoded', $request->getHeaderLine('Content-Type'));

// Inspect form body
$body = (string) $request->getBody();
parse_str($body, $params);
$this->assertSame('123', $params['id']);
$this->assertSame('test_api_key', $params['api_key']);
```

Example — manual history (pass-by-reference):

```php
$history = [];
$client = $this->makeClientWithHistory([
    new \GuzzleHttp\Psr7\Response(200, [], json_encode(['ok' => true])),
], [], $history);

$client->recipients->create([
    'firstname' => 'Jane',
    'lastname' => 'Doe',
    'address1' => '1 Example St',
    'city' => 'London',
    'postcode' => 'EC1A 1BB',
    'country' => 'GB',
]);

$this->assertNotEmpty($history);
$req = $history[0]['request'];
$this->assertSame('POST', $req->getMethod());
// For multipart requests (file uploads) inspect the Content-Type and parsed parts
$this->assertStringContainsString('multipart/form-data', $req->getHeaderLine('Content-Type'));

// Use the test helpers to parse multipart parts and assert filename
$parts = $this->parseMultipartBody($req);
$filePart = null;
foreach ($parts as $p) {
    if ($p['name'] === 'file') {
        $filePart = $p;
        break;
    }
}

$this->assertNotNull($filePart);
$this->assertSame('a.pdf', $filePart['filename']);
$this->assertStringContainsString('%PDF', $filePart['body']);
```

Notes:
- Prefer `makeClientWithHistoryPair()` in most tests — it returns a closure that always reads the up-to-date recorded history.
- For multipart bodies there isn't a convenient parser in tests; assert the `Content-Type` includes `multipart/form-data` and inspect the raw body for expected filenames or boundaries.

Quick helper: name => body map

If you only care about the parts' bodies (not filenames or disposition details) use `getMultipartParts()` which returns a simple name=>body map:

```php
[$client, $getHistory] = $this->makeClientWithHistoryPair([
    new \GuzzleHttp\Psr7\Response(200, [], json_encode(['ok' => true])),
]);

$client->files->upload(['file' => fopen('/tmp/a.pdf', 'r')]);

$history = $getHistory();
$req = $history[0]['request'];

$this->assertStringContainsString('multipart/form-data', $req->getHeaderLine('Content-Type'));
$map = $this->getMultipartParts($req);
$this->assertArrayHasKey('file', $map);
$this->assertStringContainsString('%PDF', $map['file']);
```

### Tools

```php
// Get list of countries
$countries = $client->tools->getCountries();

// Get regions for a country
$regions = $client->tools->getRegions('US');

// Get pricing
$pricing = $client->tools->getPricing();
```

Utilities and Countries helper

This SDK includes a small utilities module under `src/Utilities/`.

- `Phannp\Utilities\Countries` exposes helpers for country validation. Use `Countries::allowedCodes()` to get the canonical list of allowed country codes used by the SDK (currently `['US', 'GB']`) and `Countries::isValid($code)` to check validity.

Example:

```php
use Phannp\Utilities\Countries;

$allowed = Countries::allowedCodes(); // ['US', 'GB']
if (!Countries::isValid('US')) {
    // handle invalid
}
```

### SMS

```php
// Send an SMS
$sms = $client->sms->send([
    'to' => '+1234567890',
    'message' => 'Hello from Phannp!',
]);

// Get an SMS
$sms = $client->sms->get(123);

// List SMS messages
$messages = $client->sms->list(['limit' => 10]);
```

## Error Handling

The SDK throws exceptions when API requests fail:

```php
use Phannp\Client;
use Phannp\Exceptions\ApiException;

try {
    $client = new Client('your-api-key');
    $postcard = $client->postcards->get(123);
} catch (ApiException $e) {
    echo 'API Error: ' . $e->getMessage();
}
```

## Changelog (recent)

- 2025-10-09: Refactor of Resources API
    - `Letters::create()` now accepts a `recipient` parameter (id or array) and supports `template`, `file` (path/resource/URL), `duplex`, `clearzone`, and other flags.
    - `Letters::post()` added for posting pre-merged PDF/DOC files (accepts country and file resource/URL).
    - `Postcards::create()` simplified to accept a `size` string (e.g. '4x6'); other postcard parameters will be added as SDK support evolves.
    - Tests and README updated to reflect these API changes.


More detailed error handling

The SDK also defines `Phannp\Exceptions\PhannpException` for client-side errors (validation, argument issues) and `Phannp\Exceptions\ApiException` for server/API errors. Example showing both:

```php
use Phannp\Client;
use Phannp\Exceptions\ApiException;
use Phannp\Exceptions\PhannpException;

try {
    $client = new Client('your-api-key');
    // This may throw PhannpException for client-side validation
    $client->addresses->validate(['address1' => '1', 'country' => 'XX']);
} catch (PhannpException $e) {
    // Validation or SDK usage error (e.g. wrong parameter type)
    echo 'Client error: ' . $e->getMessage();
} catch (ApiException $e) {
    // HTTP/API error. ApiException wraps the underlying Guzzle exception.
    // You can inspect the HTTP status code and upstream message.
    echo 'API Error: ' . $e->getMessage();
    $code = $e->getCode();
    // For detailed debugging you can inspect the previous exception
    $previous = $e->getPrevious();
    if ($previous) {
        echo '\nUpstream: ' . $previous->getMessage();
    }
}

Inspecting API response JSON

ApiException exposes helpers to inspect the HTTP details returned by the API. Use `getResponseJson()` to attempt decoding a JSON body (returns an associative array or `null` if decoding fails):

```php
try {
    $client->campaigns->approve(123);
} catch (ApiException $e) {
    $status = $e->getStatusCode();
    $raw = $e->getResponseBody();
    $json = $e->getResponseJson();

    echo "Status: {$status}\n";
    if ($json) {
        // Inspect structured error fields
        echo 'API message: ' . ($json['message'] ?? $json['error'] ?? json_encode($json));
    } else {
        // Fallback to raw body
        echo 'Raw response: ' . $raw;
    }
}
```
```

## License

MIT

## Testing

This repository includes a PHPUnit test suite that exercises the `Phannp\Client` and every resource under `src/Resources`.

What the tests cover
- Each resource class has a corresponding test in `tests/Resources/` that verifies the public methods delegate to the client and return the parsed API response.
- `tests/ClientTest.php` ensures the `Client` can send GET/POST/PUT/DELETE requests and that the resource properties (e.g. `$client->postcards`) are correctly instantiated.
- Tests are isolated from the network by using Guzzle's `MockHandler`, so no real API calls or API keys are required.

How the tests work
- `tests/TestCase.php` provides a `makeClient(array $responses = [], array $options = [])` helper. It builds a `Phannp\Client` configured with a Guzzle `MockHandler` preloaded with the responses you provide.
- Each test queues one or more `GuzzleHttp\Psr7\Response` objects. When the SDK makes an HTTP request, the MockHandler returns the next queued response.

Run tests locally

1. Install dependencies (from the project root):

```bash
composer install
```

2. Run the test suite with the vendor PHPUnit binary:

```bash
./vendor/bin/phpunit --configuration phpunit.xml.dist
```

Run an individual test file:

```bash
./vendor/bin/phpunit tests/Resources/PostcardsTest.php
```

Continuous Integration

The tests are self-contained and suitable for CI. A simple GitHub Actions workflow should install composer dependencies and run `./vendor/bin/phpunit --configuration phpunit.xml.dist`.

Extending the tests
- To assert the exact request URI/method or headers, add a middleware to the Guzzle handler stack in `tests/TestCase.php` and capture the request objects for assertions.
- Add negative/error cases by queuing responses with non-2xx status codes and asserting `Phannp\Exceptions\ApiException` is thrown.
