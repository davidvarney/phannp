<?php

namespace Phannp\Resources;

class Events extends Resource
{
    public function list(array $params = []): array
    {
        return $this->client->get('events/list', $params);
    }
    
    public function get(int $id): array
    {
        return $this->client->get("events/get/{$id}");
    }
}
