<?php

namespace Phannp\Resources;

class Addresses extends Resource
{
    public function validate(array $data): array
    {
        return $this->client->post('addresses/validate', $data);
    }

    public function autocomplete(string $postcode): array
    {
        return $this->client->get('addresses/autocomplete', ['postcode' => $postcode]);
    }
}
