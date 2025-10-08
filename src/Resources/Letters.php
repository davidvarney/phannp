<?php

namespace Phannp\Resources;

class Letters extends Resource
{
    public function create(array $data): array
    {
        return $this->client->post('letters/create', $data);
    }

    public function get(int $id): array
    {
        return $this->client->get("letters/get/{$id}");
    }

    public function list(array $params = []): array
    {
        return $this->client->get('letters/list', $params);
    }

    public function cancel(int $id): array
    {
        return $this->client->post("letters/cancel/{$id}");
    }
}
