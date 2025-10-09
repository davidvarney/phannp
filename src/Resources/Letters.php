<?php

namespace Phannp\Resources;

class Letters extends Resource
{
    /**
     * Create a letter and perform a mail merge to put the address and any variable data in place.
     * You can specify a template or provide the content directly.
     *
     * @link https://www.stannp.com/us/direct-mail-api/letters#createLetter
     *
     * @param mixed  $recipient       mandatory Either an ID of an existing recipient or a new recipient array.
     * @param bool   $test            optional  If set to true, a sample PDF file will be produced but the item will
     *                                          not be dispatched and no charge will be taken.
     * @param mixed  $template        optional  An ID of a template already set up on the platform.
     * @param mixed  $file            optional  Alternatively to using the template or pages parameters,
     *                                          you can send a PDF/DOC file directly. Maximum of 10 pages.
     * @param bool   $duplex          optional  Set to false if you only want to print on the front of each page.
     *                                          Defaults to true.
     * @param bool   $clearzone       optional  If true, we will overlay clear zones with a white background.
     *                                          Defaults to false.
     * @param bool   $post_unverified optional  Default is true. If set to false, we will not post the item if the
     *                                          recipient address could not be verified.
     * @param string $tags            optional  Comma-separated tags for your reference which you can search by
     *                                          in reporting.
     * @param string $addons          optional  Use addon codes to upgrade your letter
     *                                          e.g., FIRST_CLASS to send your letter using first-class postage.
     * @return array
     * @throws \Phannp\Exceptions\ApiException on HTTP or API errors
     */
    public function create(
        mixed $recipient,
        bool $test = false,
        mixed $template = null,
        mixed $file = null,
        bool $duplex = true,
        bool $clearzone = false,
        bool $post_unverified = true,
        string $tags = '',
        string $addons = ''
    ): array {
        $data = [
            'recipient'         => $recipient,
            'test'              => $test,
            'template'          => $template,
            'file'              => $file,
            'duplex'            => $duplex,
            'clearzone'         => $clearzone,
            'post_unverified'   => $post_unverified,
            'tags'              => $tags,
            'addons'            => $addons,
        ];

        return $this->client->post('letters/create', $data);
    }

    /**
     * Post a single letter that already has an address on the PDF file.
     * Use this endpoint if you have already mail-merged your letter and it meets our design guidelines.
     *
     * @link https://www.stannp.com/us/direct-mail-api/letters#postLetter
     *
     * @param string $country       mandatory  ISO alpha-2 country code, e.g., US, CA, GB, FR, DE.
     * @param bool   $test          optional   If set to true, a sample PDF file will be produced but the item
     *                                         will not be dispatched and no charge will be taken.
     * @param mixed  $file          optional   A URL or binary file of the PDF file to print and post.
     * @param bool   $duplex        optional   Defaults to true.
     * @param bool   $transactional optional   Use this for sensitive data. Defaults to false.
     * @param string $tags          optional   Comma-separated tags for your reference which you can search by
     *                                         in reporting.
     * @param bool   $ocr           optional   If set to true, we will try to read the address from the window
     *                                         clear zone and validate the address.
     */
    public function post(
        string $country,
        bool $test = false,
        mixed $file = null,
        bool $duplex = true,
        bool $transactional = false,
        string $tags = '',
        bool $ocr = false
    ): array {
        return $this->client->post('letters/post', [
            'country'       => $country,
            'test'          => $test,
            'file'          => $file,
            'duplex'       => $duplex,
            'transactional' => $transactional,
            'tags'         => $tags,
            'ocr'          => $ocr,
        ]);
    }

    /**
     * Get the details of a letter by its ID.
     * 
     * @link https://www.stannp.com/us/direct-mail-api/letters#getLetter
     * 
     * @param int $id The letter ID
     * @return array
     * @throws \Phannp\Exceptions\ApiException on HTTP or API errors
     */
    public function get(int $id): array
    {
        return $this->client->get("letters/get/".$id);
    }

    /**
     * Cancel a letter by its ID.
     * 
     * @link https://www.stannp.com/us/direct-mail-api/letters#cancelLetter
     * 
     * @param int $id The letter ID
     * @return array
     * @throws \Phannp\Exceptions\ApiException on HTTP or API errors
     */
    public function cancel(int $id): array
    {
        return $this->client->post("letters/cancel/".$id);
    }
}
