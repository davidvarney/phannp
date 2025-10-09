<?php

namespace Phannp\Resources;

class Groups extends Resource
{
    /**
     * Create a new group
     *
     * @link https://www.stannp.com/us/direct-mail-api/groups#create_group
     *
     * @param string $name The name of the group
     * @return array
     * @throws \Phannp\Exceptions\ApiException on HTTP or API errors
     */
    public function create(string $name): array
    {
        return $this->client->post('groups', ['name' => $name]);
    }

    /**
     * List groups with optional pagination parameters.
     *
     * @link https://www.stannp.com/us/direct-mail-api/groups#list_groups
     *
     * @param int|null $offset Optional offset for pagination
     * @param int|null $limit Optional limit for pagination
     * @return array
     * @throws \Phannp\Exceptions\ApiException on HTTP or API errors
     */
    public function list(?int $offset = null, ?int $limit = null): array
    {
        $params = [];
        if ($offset !== null) {
            $params['offset'] = $offset;
        }
        if ($limit !== null) {
            $params['limit'] = $limit;
        }

        return $this->client->get('groups/list', $params);
    }

    /**
     * Delete a group. The recipients will remain on your account if delete_recipients is set to false.
     *
     * @link https://www.stannp.com/us/direct-mail-api/groups#delete_group
     *
     * @param int $id The group ID
     * @param bool $delete_recipients Whether to delete the recipients in the group
     * @return array
     * @throws \Phannp\Exceptions\ApiException on HTTP or API errors
     */
    public function delete(int $id, bool $delete_recipients = false): array
    {
        return $this->client->delete("groups/delete/" . $id, ['delete_recipients' => $delete_recipients]);
    }

    /**
     * Add a recipient(s) to a group
     *
     * @link https://www.stannp.com/us/direct-mail-api/groups#add_recipients
     *
     * @param int $groupId The group ID
     * @param string $recipientIds A comma-separated string of recipient IDs to add
     * @return array
     * @throws \Phannp\Exceptions\ApiException on HTTP or API errors
     */
    public function addRecipients(int $groupId, string $recipientIds): array
    {
        return $this->client->post("groups/" . $groupId . "/recipients", ['recipients' => $recipientIds]);
    }

    /**
     * Remove a recipient(s) from a group
     *
     * @link https://www.stannp.com/us/direct-mail-api/groups#remove_recipients
     *
     * @param int $groupId The group ID
     * @param string $recipientIds A comma-separated string of recipient IDs to remove
     * @return array
     * @throws \Phannp\Exceptions\ApiException on HTTP or API errors
     */
    public function removeRecipients(int $groupId, string $recipientIds): array
    {
        return $this->client->delete("groups/" . $groupId . "/recipients", ['recipients' => $recipientIds]);
    }

    /**
     * Remove all recipients from the mailing list.
     * The recipients will remain on your account if delete_recipients is set to false.
     *
     * @link https://www.stannp.com/us/direct-mail-api/groups#purge_group
     *
     * @param int $groupId The group ID
     * @param bool $delete_recipients Whether to remove the recipients from your account
     * @return array
     * @throws \Phannp\Exceptions\ApiException on HTTP or API errors
     */
    public function purge(int $groupId, bool $delete_recipients = false): array
    {
        return $this->client->post("groups/" . $groupId . "/purge", ['delete_recipients' => $delete_recipients]);
    }

    /**
     * Recalculate the number of recipients in a group.
     * This is useful if you have added or removed recipients outside of the API.
     *
     * @link https://www.stannp.com/us/direct-mail-api/groups#recalculate_group
     *
     * @param int $groupId The group ID
     * @return array
     * @throws \Phannp\Exceptions\ApiException on HTTP or API errors
     */
    public function recalculate(int $groupId): array
    {
        return $this->client->post("groups/" . $groupId . "/recalculate");
    }
}
