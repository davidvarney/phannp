<?php

namespace Phannp\Resources;

class Recipients extends Resource
{
    public function create(array $data): array
    {
        return $this->client->post('recipients/new', $data);
    }
    
    public function get(int $id): array
    {
        return $this->client->get("recipients/get/{$id}");
    }
    
    public function list(array $params = []): array
    {
        return $this->client->get('recipients/list', $params);
    }
    
    public function delete(int $id): array
    {
        return $this->client->delete("recipients/delete/{$id}");
    }
    
    public function import(array $data): array
    {
        return $this->client->post('recipients/import', $data);
    }
}
