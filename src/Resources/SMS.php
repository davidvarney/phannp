<?php

namespace Phannp\Resources;

class SMS extends Resource
{
    /**
     * Send an SMS message to a recipient's mobile device
     *
     * @link https://www.stannp.com/us/direct-mail-api/sms#send_sms
     *
     * @param string $message       mandatory  The message to be sent.
     *                                         You can use template tags if using recipient_id (e.g., Hi {firstname}).
     * @param bool   $test          optional   If set to true, the SMS message will not be sent and there
     *                                         will be no charge.
     * @param string $phoneNumber   optional   The recipient's phone number. Required if recipient_id is not provided.
     * @param int    $recipientId   optional   ID of a recipient that has already been added to your account.
     *                                         Required if phone_number is not provided.
     * @param string $country       optional   A 2-character country code (e.g., US, CA, GB).
     *                                         Defaults to your account region.
     *
     * @return array
     * @throws \Phannp\Exceptions\ApiException on HTTP or API errors
     */
    public function create(
        string $message,
        bool $test = false,
        ?string $phoneNumber = null,
        ?int $recipientId = null,
        ?string $country = null
    ): array {
        // Require either a phone number or recipient id
        if (($phoneNumber === null || trim($phoneNumber) === '') && $recipientId === null) {
            throw new \InvalidArgumentException('Either phoneNumber or recipientId must be provided');
        }

        // Normalize and validate phone number if provided
        if ($phoneNumber !== null) {
            $normalizedPhone = preg_replace('/[\s\-()\.]/', '', $phoneNumber);
            if (!preg_match('/^\+\d{8,15}$/', $normalizedPhone)) {
                throw new \InvalidArgumentException('phoneNumber must be in E.164 format (e.g. +447911123456)');
            }
            $phoneNumber = $normalizedPhone;
        }

        // Validate country code if provided
        if ($country !== null && !\Phannp\Utilities\Countries::isValid($country)) {
            throw new \InvalidArgumentException('country must be a valid ISO 3166-1 alpha-2 code');
        }

        $data = [
            'message' => $message,
            'test' => $test,
            'phone_number' => $phoneNumber,
            'recipient_id' => $recipientId,
            'country' => $country,
        ];

        return $this->client->post('sms/create', $data);
    }
}
