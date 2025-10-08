<?php

namespace Phannp\Resources;

class Account extends Resource
{
    public function get(): array
    {
        return $this->client->get('users/me');
    }
    
    public function getBalance(): array
    {
        return $this->client->get('account/balance');
    }
    
    public function topUp(array $data): array
    {
        return $this->client->post('account/topup', $data);
    }
}
