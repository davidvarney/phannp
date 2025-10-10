<?php

namespace Phannp\Resources;

class Recipients extends Resource
{
    /**
     * Create a new recipient
     *
     * @link https://www.stannp.com/us/direct-mail-api/recipients#createRecipient
     *
     * @param string $firstname     Recipient's first name.
     * @param string $lastname      Recipient's last name.
     * @param string $address1      Address line 1.
     * @param string $address2      Address line 2.
     * @param string $address3      Address line 3.
     * @param string $city          Address city.
     * @param string $postcode      Address postal code.
     * @param string $country       ISO 3166-1 Alpha 2 Country Code (GB,US,FR...).
     * @param string $email         The recipient's email address.
     * @param string $phone_number  The recipient's phone number.
     * @param string $ref_id        This is an alternative ID.
     *                              You can use an ID from a different service so you can always
     *                              match a recipient across multiple services.
     * @param int    $group_id      The group ID you wish to add the data to.
     * @param string $on_duplicate  What to do if a duplicate is found (update/ignore/duplicate).
     * @param string $test_level    This is how we test to see if this recipient is a duplicate record.
     *                              You can choose from the following. Defaults to 'fullname'.
     *                              - 'email'    - This will match a duplicate based on the email address.
     *                              - 'fullname' - This will match a duplicate based on the full name and address.
     *                              - 'initial'  - This will match a duplicate based on the initial of the first
     *                                             plus last name and address.
     *                              - 'ref_id'   - This will match a duplicate on any alternative ID you have stored.
     *                              If you have added custom fields to your recipients you can also add them as
     *                              parameters when adding new recipient records.
     * @return array
     * @throws \Phannp\Exceptions\ApiException on HTTP or API errors
     */
    public function create(
        string $firstname,
        string $lastname,
        string $address1,
        string $address2,
        string $address3,
        string $city,
        string $postcode,
        string $country,
        string $email,
        string $phone_number,
        string $ref_id,
        int $group_id,
        string $on_duplicate,
        string $test_level
    ): array {
        return $this->client->post('recipients/new', [
            'firstname'     => $firstname,
            'lastname'      => $lastname,
            'address1'      => $address1,
            'address2'      => $address2,
            'address3'      => $address3,
            'city'          => $city,
            'postcode'      => $postcode,
            'country'       => $country,
            'email'         => $email,
            'phone_number'  => $phone_number,
            'ref_id'        => $ref_id,
            'group_id'      => $group_id,
            'on_duplicate'  => $on_duplicate,
            'test_level'    => $test_level,
        ]);
    }

    /**
     * Retrieve a single recipient by ID.
     *
     * @link https://www.stannp.com/us/direct-mail-api/recipients#getRecipient
     *
     * @param int $id The recipient ID
     * @return array
     * @throws \Phannp\Exceptions\ApiException on HTTP or API errors
     */
    public function get(int $id): array
    {
        return $this->client->get("recipients/get/" . $id);
    }

    /**
     * Obtain a list of recipient objects.
     * Include group_id to only return recipients in the specified group.
     * You can use the offset and limit parameters for pagination.
     *
     * @link https://www.stannp.com/us/direct-mail-api/recipients#listRecipients
     *
     * @param int $groupId optional Group ID to filter recipients
     * @param int $offset  optional Offset for pagination (default 0)
     * @param int $limit   optional Limit for pagination (default 0, meaning no limit)
     * @return array
     * @throws \Phannp\Exceptions\ApiException on HTTP or API errors
     */
    public function list(int $groupId, int $offset = 0, int $limit = 0): array
    {
        $params = [
            'group_id' => $groupId,
            'offset'   => $offset,
            'limit'    => $limit,
        ];

        return $this->client->get('recipients/list', $params);
    }

    /**
     * Delete a recipient by ID.
     *
     * @link https://www.stannp.com/us/direct-mail-api/recipients#deleteRecipient
     *
     * @param int $id The recipient ID
     * @return array
     * @throws \Phannp\Exceptions\ApiException on HTTP or API errors
     */
    public function delete(int $id): array
    {
        return $this->client->delete("recipients/delete/" . $id);
    }

    /**
     * Delete all recipients.
     *
     * @link https://www.stannp.com/us/direct-mail-api/recipients#deleteAllRecipients
     *
     * @param bool $deleteAll Whether to delete all recipients (default false)
     * @return array
     * @throws \Phannp\Exceptions\ApiException on HTTP or API errors
     */
    public function deleteAll(bool $deleteAll = false): array
    {
        return $this->client->delete("recipients/deleteAll", ['delete_all' => $deleteAll]);
    }

    /**
     * Import multiple recipients in bulk.
     *
     * @link https://www.stannp.com/us/direct-mail-api/recipients#importRecipients
     *
     * @param string $file         A CSV or XLS file as binary format, a base64 encoded string of a CSV file,
     *                             or a URL to a file.
     * @param int    $group_id     ID of the group you wish to import data into.
     * @param string $duplicates   What to do if a duplicate is found (update/ignore/duplicate).
     *                             update = [default] Updates the duplicated record with newest data.
     *                             ignore = do not add the new duplicated record.
     *                             duplicate = Add all duplicate records.
     * @param bool   $no_headings  True or false. If your CSV file hasn't got a row of heading names at the top
     *                             you can set this value to true. If you do not have a heading row then you
     *                             must supply the headings by using the following mappings parameter.
     * @param string $mappings     If your CSV file has a heading row with names that differ to our names then
     *                             you can pass a comma separated string here to remap the headings.
     *                             eg: title, firstname, lastname, company, address1, address2, city, postcode,
     *                             country, custom, custom, skip. 'custom' means we will create a new field name
     *                             matching your heading name. 'skip' will ignore the column so it is not imported.
     *                             If you do not use this parameter then your heading names must match ours exactly.
     *                             title, firstname,lastname, company, job_title, address1, address2, address3, city,
     *                             postcode, country.
     *
     * @return array
     * @throws \Phannp\Exceptions\ApiException on HTTP or API errors
     */
    public function import(string $file, int $group_id, string $duplicates, bool $no_headings, string $mappings): array
    {
        $data = [
            'file'          => $file,
            'group_id'      => $group_id,
            'duplicates'    => $duplicates,
            'no_headings'   => $no_headings,
            'mappings'      => $mappings,
        ];

        return $this->client->post('recipients/import', $data);
    }
}
