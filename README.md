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
$postcard = $client->postcards->create([
    'recipient' => 123,
    'template' => 456,
    'size' => 'A6',
]);

// Get a postcard
$postcard = $client->postcards->get(789);

// List postcards
$postcards = $client->postcards->list(['limit' => 10]);

// Cancel a postcard
$result = $client->postcards->cancel(789);
```

### Letters

```php
// Create a letter
$letter = $client->letters->create([
    'recipient' => 123,
    'template' => 456,
    'size' => 'A4',
]);

// Get a letter
$letter = $client->letters->get(789);

// List letters
$letters = $client->letters->list(['limit' => 10]);

// Cancel a letter
$result = $client->letters->cancel(789);
```

### Recipients

```php
// Create a recipient
$recipient = $client->recipients->create([
    'firstname' => 'John',
    'lastname' => 'Doe',
    'address1' => '123 Main St',
    'city' => 'New York',
    'postcode' => '10001',
    'country' => 'US',
]);

// Get a recipient
$recipient = $client->recipients->get(123);

// List recipients
$recipients = $client->recipients->list(['limit' => 10]);

// Delete a recipient
$result = $client->recipients->delete(123);

// Import recipients
$result = $client->recipients->import(['file' => 'path/to/file.csv']);
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
// List events
$events = $client->events->list(['limit' => 10]);

// Get an event
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

### Tools

```php
// Get list of countries
$countries = $client->tools->getCountries();

// Get regions for a country
$regions = $client->tools->getRegions('US');

// Get pricing
$pricing = $client->tools->getPricing();
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

## License

MIT
