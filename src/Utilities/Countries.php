<?php

namespace Phannp\Utilities;

class Countries
{
    /**
     * Minimal list of ISO 3166-1 alpha-2 country codes for validation.
     * Expand this list as needed.
     *
     * @return array
     */
    public static function list(): array
    {
        return [
            'US','GB'
        ];
    }

    /**
     * Public helper to return the allowed country codes for external use.
     *
     * This keeps allowed values DRY between code, documentation and error messages.
     *
     * @return array
     */
    public static function allowedCodes(): array
    {
        return self::list();
    }

    public static function isValid(string $code): bool
    {
        return in_array(strtoupper($code), self::list(), true);
    }
}
