<?php

namespace Phannp\Resources;

/**
 * Class Account
 *
 * @package Phannp\Resources
 *
 * @link https://www.stannp.com/us/direct-mail-api/account
 */
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

    /**
     * Get account balance
     *
     * @link https://www.stannp.com/us/direct-mail-api/account
     *
     * @return array
     * @throws \Phannp\Exceptions\ApiException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getBalance(): array
    {
        return $this->client->get('account/balance');
    }

    /**
     * Get account details
     *
     * Top up your balance if you have a saved card and set one to default.
     * @link https://www.stannp.com/us/direct-mail-api/account
     *
     * @return array
     * @throws \Phannp\Exceptions\ApiException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function topUp(array $data): array
    {
        return $this->client->post('account/topup', $data);
    }
}
