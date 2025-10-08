<?php

namespace Phannp\Resources;

class Campaigns extends Resource
{
    public function create(array $data): array
    {
        return $this->client->post('campaigns/create', $data);
    }
    
    public function get(int $id): array
    {
        return $this->client->get("campaigns/get/{$id}");
    }
    
    public function list(array $params = []): array
    {
        return $this->client->get('campaigns/list', $params);
    }
    
    public function delete(int $id): array
    {
        return $this->client->delete("campaigns/delete/{$id}");
    }
}
