<?php

namespace Phannp\Resources;

class Groups extends Resource
{
    public function create(array $data): array
    {
        return $this->client->post('groups/new', $data);
    }

    public function get(int $id): array
    {
        return $this->client->get("groups/get/{$id}");
    }

    public function list(array $params = []): array
    {
        return $this->client->get('groups/list', $params);
    }

    public function delete(int $id): array
    {
        return $this->client->delete("groups/delete/{$id}");
    }

    public function addRecipient(int $groupId, int $recipientId): array
    {
        return $this->client->post("groups/add_recipient/{$groupId}/{$recipientId}");
    }

    public function removeRecipient(int $groupId, int $recipientId): array
    {
        return $this->client->delete("groups/remove_recipient/{$groupId}/{$recipientId}");
    }
}
