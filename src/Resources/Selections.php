<?php

namespace Phannp\Resources;

class Selections extends Resource
{
    public function create(array $data): array
    {
        return $this->client->post('selections/create', $data);
    }
    
    public function get(int $id): array
    {
        return $this->client->get("selections/get/{$id}");
    }
    
    public function list(array $params = []): array
    {
        return $this->client->get('selections/list', $params);
    }
}
