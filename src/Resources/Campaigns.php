<?php

namespace Phannp\Resources;

/**
 * Class Campaigns
 *
 * @package Phannp\Resources
 *
 * @link https://www.stannp.com/us/direct-mail-api/campaigns
 */
class Campaigns extends Resource
{
    /**
     * Create a new campaign.
     * 
     * @link https://www.stannp.com/us/direct-mail-api/campaigns#create
     * 
     * The method will throw an ApiException if the API request fails.
     * 
     * Allowed parameters:
     * - name (string, required)
     * - type (string, required, one of 'a6-postcard', 'a5-postcard', 'letter', 'sms')
     * - template_id (integer, required, template ID to use for the campaign)
     * - file (string, A single or multi-page PDF file to use as the design artwork. Can be a URL or binary file.)
     * - front (string, optional, A PDF or JPG file to use as the front image. Can be a URL or binary file)
     * - back (string, optional, A PDF or JPG file to use as the back image. Can be a URL or binary file)
     * - size (string, optional, The size of the campaign. Can be 'A6', 'A5', 'letter', or 'custom')
     * - save (bool, optional, If front or back images are used, save uploaded file as a new template on your account)
     * - group_id (integer, optional, The ID of the group to which the campaign will use the recipients from)
     * - what_recipients (string, optional,
     *     "all" = every recipient in group.
     *     "valid" sends to only validated addresses.
     *     "not_valid" sends to only non-validated addresses.
     *     "int" sends to only international addresses.)
     * - addons (string, optional, If you have an addon code)
     *   
     * @param array $data
     * @return array
     * @throws \Phannp\Exceptions\ApiException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function create(array $data): array
    {
        // Allowed parameters per docblock
        $allowed = [
            'name',
            'type',
            'template_id',
            'file',
            'front',
            'back',
            'size',
            'save',
            'group_id',
            'what_recipients',
            'addons',
        ];

        $unknown = array_diff(array_keys($data), $allowed);
        if (!empty($unknown)) {
            throw new \Phannp\Exceptions\PhannpException('Unknown parameter(s) for Campaigns::create: ' . implode(', ', $unknown));
        }

        // Required: name
        if (!isset($data['name']) || !is_string($data['name']) || $data['name'] === '') {
            throw new \Phannp\Exceptions\PhannpException('Parameter "name" is required and must be a non-empty string.');
        }

        // Required: type
        if (!isset($data['type']) || !is_string($data['type'])) {
            throw new \Phannp\Exceptions\PhannpException('Parameter "type" is required and must be a string.');
        }

        $allowedTypes = ['a6-postcard', 'a5-postcard', 'letter', 'sms'];
        if (!in_array(strtolower($data['type']), $allowedTypes, true)) {
            throw new \Phannp\Exceptions\PhannpException('Parameter "type" must be one of: ' . implode(', ', $allowedTypes));
        }

        // Required: template_id
        if (!isset($data['template_id']) || !is_int($data['template_id'])) {
            throw new \Phannp\Exceptions\PhannpException('Parameter "template_id" is required and must be an integer.');
        }

        // Optional fields type checks
        if (isset($data['file']) && !is_string($data['file']) && !is_resource($data['file'])) {
            throw new \Phannp\Exceptions\PhannpException('Parameter "file" must be a string (URL or file path) or a resource when provided.');
        }

        if (isset($data['front']) && !is_string($data['front']) && !is_resource($data['front'])) {
            throw new \Phannp\Exceptions\PhannpException('Parameter "front" must be a string (URL or file path) or a resource when provided.');
        }

        if (isset($data['back']) && !is_string($data['back']) && !is_resource($data['back'])) {
            throw new \Phannp\Exceptions\PhannpException('Parameter "back" must be a string (URL or file path) or a resource when provided.');
        }

        if (isset($data['size']) && !is_string($data['size'])) {
            throw new \Phannp\Exceptions\PhannpException('Parameter "size" must be a string when provided.');
        }

        if (isset($data['save']) && !is_bool($data['save'])) {
            throw new \Phannp\Exceptions\PhannpException('Parameter "save" must be a boolean when provided.');
        }

        if (isset($data['group_id']) && !is_int($data['group_id'])) {
            throw new \Phannp\Exceptions\PhannpException('Parameter "group_id" must be an integer when provided.');
        }

        if (isset($data['what_recipients'])) {
            $wr = $data['what_recipients'];
            $allowedWr = ['all', 'valid', 'not_valid', 'int'];
            if (!is_string($wr) || !in_array($wr, $allowedWr, true)) {
                throw new \Phannp\Exceptions\PhannpException('Parameter "what_recipients" must be one of: ' . implode(', ', $allowedWr));
            }
        }

        if (isset($data['addons']) && !is_string($data['addons'])) {
            throw new \Phannp\Exceptions\PhannpException('Parameter "addons" must be a string when provided.');
        }



        // Normalize type to lowercase
        if (isset($data['type'])) {
            $data['type'] = strtolower($data['type']);
        }

        return $this->client->post('campaigns/create', $data);
    }

    /**
     * Get a campaign by ID.
     * 
     * @link https://www.stannp.com/us/direct-mail-api/campaigns#get
     *
     * @param int $id
     * @return array
     * @throws \Phannp\Exceptions\ApiException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function get(int $id): array
    {
        return $this->client->get("campaigns/get/{$id}");
    }

    /**
     * List campaigns.
     * 
     * @link https://www.stannp.com/us/direct-mail-api/campaigns#list
     * 
     * @param array $params Optional query parameters
     */
    public function list(array $params = []): array
    {
        return $this->client->get('campaigns/list', $params);
    }

    public function delete(int $id): array
    {
        return $this->client->delete("campaigns/delete/{$id}");
    }

    /**
     * Produce a sample of a campaign by ID.
     * 
     * @link https://www.stannp.com/us/direct-mail-api/campaigns#sample
     * 
     * @param int $id
     * @return array
     * @throws \Phannp\Exceptions\ApiException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function produceASample(int $id): array
    {
        return $this->client->get("campaigns/sample", ['id' => $id]);
    }

    /**
     * Approve a campaign by ID.
     * 
     * @link https://www.stannp.com/us/direct-mail-api/campaigns#approve
     * 
     * @param int $id
     * @return array
     * @throws \Phannp\Exceptions\ApiException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function approve(int $id): array
    {
        return $this->client->post("campaigns/approve", ['id' => $id]);
    }
}
