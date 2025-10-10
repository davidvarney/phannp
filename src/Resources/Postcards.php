<?php

namespace Phannp\Resources;

class Postcards extends Resource
{
    /**
     * Create a new postcard
     *
     * @link https://www.stannp.com/us/direct-mail-api/postcards#createPostcard
     *
     * @param string $size The size of the postcard, e.g., '4x6', '6x9', '6x11'
     * @todo I know there are more parameters here, but the docs are not clear.
     *       I'm thinking that their API docs/site are under construction at the
     *       moment or there's not much care about it.
     */
    public function create(string $size): array
    {
        $data = [
            'size' => $size,
        ];

        return $this->client->post('postcards/create', $data);
    }

    /**
     * Get the details of a postcard by its ID.
     *
     * @link https://www.stannp.com/us/direct-mail-api/postcards#getPostcard
     *
     * @param int $id The postcard ID
     * @return array
     * @throws \Phannp\Exceptions\ApiException on HTTP or API errors
     */
    public function get(int $id): array
    {
        return $this->client->get("postcards/get/" . $id);
    }

    /**
     * Cancel a postcard by its ID.
     *
     * @link https://www.stannp.com/us/direct-mail-api/postcards#cancelPostcard
     *
     * @param int $id The postcard ID
     * @return array
     * @throws \Phannp\Exceptions\ApiException on HTTP or API errors
     */
    public function cancel(int $id): array
    {
        return $this->client->post("postcards/cancel/" . $id);
    }
}
