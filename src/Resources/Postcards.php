<?php

namespace Phannp\Resources;

class Postcards extends Resource
{
    /**
     * Create a new postcard
     *
     * @link https://www.stannp.com/us/direct-mail-api/postcards#createPostcard
     *
     * @param array $data An array of options. Options must include 'size' => '4x6'|'6x9'|'6x11', etc.
     *
     * Changelog:
     * - 2025-10-20: `create()` now requires an associative array and must include a 'size' key
     *               (e.g. `['size' => '4x6']`). Previous behavior accepting a bare size string
     *               was removed to enforce explicit parameter passing.
     * @todo I know there are more parameters here, but the docs are not clear.
     *       I'm thinking that their API docs/site are under construction at the
     *       moment or there's not much care about it.
     */
    public function create(array $data): array
    {
        // Require a size to be present.
        if (!isset($data['size']) || empty($data['size'])) {
            throw new \InvalidArgumentException('size must be a valid postcard size, EX: "4x6", "6x9", "6x11"');
        }

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
