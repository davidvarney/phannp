<?php

namespace Phannp\Resources;

/**
 * Class Events
 * @package Phannp\Resources
 *
 * @method array create(string $recipient_id, string $name, string $value, bool $conversion = false, string $data = '', string $ref = '')
 * @link https://www.stannp.com/us/direct-mail-api/events
 */
class Events extends Resource
{
    /**
     * Create a new event
     * 
     * @param string $recipient_id	An id of the recipient. This needs to be the recipient_id or the alternative reference
     *                              id which can be used to match an id from a different system.
     * @param string $name	        Name the event. For example: PURCHASE, SIGNUP, PAGE_VIEW, PRODUCT_VIEW, PRODUCT_TO_BASKET.
     * @param string $value	        Add value information. For example, the value of the purchase or the product name.
     * @param bool   $conversion	True or false. Is this a conversion
     *                              event (e.g., purchasing or a signup)? Defaults to false.
     * @param string $data	        Any extended data you wish to store about this event
     *                              for automation tasks or dynamic templating.
     * @param string $ref	        Can be a campaign reference id or a mailpiece reference id. If left empty, we will
     *                              use any recent communication to allocate as a reference.
     * @return array
     * @throws \Phannp\Exceptions\ApiException on HTTP or API errors
     */
    public function create(string $recipient_id, string $name, string $value, bool $conversion = false, string $data = '', string $ref = ''): array
    {
        return $this->client->post('events/create', [
            'recipient_id'  => $recipient_id,
            'name'          => $name,
            'value'         => $value,
            'conversion'    => $conversion,
            'data'          => $data,
            'ref'           => $ref,
        ]);
    }
}
