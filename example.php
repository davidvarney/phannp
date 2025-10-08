<?php

/**
 * Example usage of Phannp SDK
 * 
 * This file demonstrates how to use the Phannp SDK to interact with the Stannp API.
 */

require __DIR__ . '/vendor/autoload.php';

use Phannp\Client;
use Phannp\Exceptions\ApiException;

// Initialize the client with your API key
$apiKey = getenv('STANNP_API_KEY') ?: 'your-api-key-here';
$client = new Client($apiKey);

// Example: Get account information
try {
    echo "=== Account Information ===\n";
    // $account = $client->account->get();
    // print_r($account);
    echo "Client initialized successfully!\n";
    echo "Available resources:\n";
    echo "- Postcards\n";
    echo "- Letters\n";
    echo "- Recipients\n";
    echo "- Groups\n";
    echo "- Events\n";
    echo "- Files\n";
    echo "- Reporting\n";
    echo "- Campaigns\n";
    echo "- Selections\n";
    echo "- Account\n";
    echo "- Addresses\n";
    echo "- Tools\n";
    echo "- SMS\n";
} catch (ApiException $e) {
    echo "API Error: " . $e->getMessage() . "\n";
}

// Example: List postcards
try {
    echo "\n=== List Postcards ===\n";
    // $postcards = $client->postcards->list(['limit' => 10]);
    // print_r($postcards);
    echo "Use: \$client->postcards->list(['limit' => 10])\n";
} catch (ApiException $e) {
    echo "API Error: " . $e->getMessage() . "\n";
}

// Example: Create a recipient
try {
    echo "\n=== Create Recipient ===\n";
    // $recipient = $client->recipients->create([
    //     'firstname' => 'John',
    //     'lastname' => 'Doe',
    //     'address1' => '123 Main St',
    //     'city' => 'New York',
    //     'postcode' => '10001',
    //     'country' => 'US',
    // ]);
    // print_r($recipient);
    echo "Use: \$client->recipients->create([...data...])\n";
} catch (ApiException $e) {
    echo "API Error: " . $e->getMessage() . "\n";
}
