<?php

namespace Phannp\Resources;

use Phannp\Exceptions\PhannpException;
use Phannp\Utilities\Countries;

/**
 * Class Addresses
 *
 * @package Phannp\Resources
 *
 * @link https://www.stannp.com/us/direct-mail-api/addresses
 */
class Addresses extends Resource
{
    /**
     * Validate an address.
     *
     * Allowed parameters:
     * - company
     * - address1
     * - address2
     * - city
     * - state
     * - zipcode
     * - country
     *
     * The method will throw a PhannpException if unknown parameters are provided
     * or if any provided parameter is not a string.
     *
     * @param array $data
     * @return array
     * @throws PhannpException
     */
    public function validate(array $data): array
    {
        $allowed = ['company', 'address1', 'address2', 'city', 'state', 'zipcode', 'country'];

        $unknown = array_diff(array_keys($data), $allowed);
        if (!empty($unknown)) {
            throw new PhannpException('Unknown parameter(s) for Addresses::validate: ' . implode(', ', $unknown));
        }

        foreach ($data as $key => $value) {
            if (!is_string($value)) {
                throw new PhannpException(sprintf('Parameter "%s" must be a string.', $key));
            }

            // Additional semantic checks
            if ($key === 'country') {
                // Must be ISO 3166-1 alpha-2 (two letters)
                if (!preg_match('/^[A-Za-z]{2}$/', $value) || !Countries::isValid($value)) {
                    $allowed = Countries::allowedCodes();
                    $allowedSnippet = 'use "' . implode('" or "', $allowed) . '"';
                    throw new PhannpException('Parameter "country" must be a valid ISO 3166-1 alpha-2 country code. Stannp currently accepts only ' . implode(', ', $allowed) . ' (' . $allowedSnippet . ').');
                }
            }

            if ($key === 'state' && $value !== '') {
                // Only validate state format for countries that commonly use states/provinces
                // We'll validate after country normalization below where we know the country.
                if (!preg_match('/^[A-Za-z]{2}$/', $value)) {
                    throw new PhannpException('Parameter "state" must be a two-letter abbreviation when provided.');
                }
            }
        }

        // Normalize country/state to uppercase before sending
        if (isset($data['country'])) {
            $data['country'] = strtoupper($data['country']);
        }
        if (isset($data['state'])) {
            $data['state'] = strtoupper($data['state']);
        }

        // Conditional state validation for selected countries
        if (isset($data['country']) && isset($data['state']) && $data['state'] !== '') {
            $countries_with_states = ['US', 'CA', 'AU'];
            if (!in_array($data['country'], $countries_with_states, true)) {
                // For countries where state isn't commonly used, we accept empty or two-letter but do not require it.
                // Already validated format above, so nothing more to do.
            }
        }

        return $this->client->post('addresses/validate', $data);
    }
}
