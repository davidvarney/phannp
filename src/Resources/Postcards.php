<?php

namespace Phannp\Resources;

class Postcards extends Resource
{
    public function create(array $data): array
    {
        return $this->client->post('postcards/create', $data);
    }

    public function get(int $id): array
    {
        return $this->client->get("postcards/get/{$id}");
    }

    public function list(array $params = []): array
    {
        return $this->client->get('postcards/list', $params);
    }

    public function cancel(int $id): array
    {
        return $this->client->post("postcards/cancel/{$id}");
    }
}
