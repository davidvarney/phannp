<?php

namespace Phannp\Resources;

class Account extends Resource
{
    /**
     * Get details about the authenticated user
     *
     * This is not a part of the Stannp Account API documentation.
     * This is found under the Authentication section of the API docs.
     * The docs for this can be found at the below link
     * @link https://www.stannp.com/us/direct-mail-api/guide#authentication
     *
     *
     * @return array
     * @throws \Phannp\Exceptions\ApiException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function get(): array
    {
        return $this->client->get('users/me');
    }

    public function getBalance(): array
    {
        return $this->client->get('account/balance');
    }

    public function topUp(array $data): array
    {
        return $this->client->post('account/topup', $data);
    }
}
