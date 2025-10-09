<?php

namespace Phannp\Resources;

class Tools extends Resource
{
    /**
     * @todo These are NOT correct endpoints - need to confirm with Stannp
     */
    public function getCountries(): array
    {
        return $this->client->get('countries/list');
    }

    public function getRegions(string $countryCode): array
    {
        return $this->client->get('regions/list', ['country' => $countryCode]);
    }

    public function getPricing(): array
    {
        return $this->client->get('pricing');
    }
}
