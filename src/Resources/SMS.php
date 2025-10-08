<?php

namespace Phannp\Resources;

class SMS extends Resource
{
    public function send(array $data): array
    {
        return $this->client->post('sms/send', $data);
    }
    
    public function get(int $id): array
    {
        return $this->client->get("sms/get/{$id}");
    }
    
    public function list(array $params = []): array
    {
        return $this->client->get('sms/list', $params);
    }
}
