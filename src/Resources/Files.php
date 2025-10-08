<?php

namespace Phannp\Resources;

class Files extends Resource
{
    public function upload(array $data): array
    {
        return $this->client->post('files/upload', $data);
    }

    public function get(int $id): array
    {
        return $this->client->get("files/get/{$id}");
    }

    public function list(array $params = []): array
    {
        return $this->client->get('files/list', $params);
    }

    public function delete(int $id): array
    {
        return $this->client->delete("files/delete/{$id}");
    }
}
