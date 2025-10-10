<?php

namespace Phannp\Resources;

/**
 * Class Selections
 *
 * @package Phannp\Resources
 * @link https://www.stannp.com/us/direct-mail-api/selections
 */
class Selections extends Resource
{
    /**
     * Creates an auto filter for a group.
     *
     * @param int    $group_id  The ID of the group to associate this selection with.
     * @param string $name      Name your filter.
     * @param string $filters   A specially formatted string to represent your filters. eg:
     *                          total_spent:::more_than:::300
     *                          The format is based on the following:
     *                          [column_name]:::[string_operator]:::[value]
     *                          The string operators available are: matches, contains, begins, ends, before, after,
     *                          less_than, more_than, is_not.
     *                          You can chain multiple filters together like this:
     *                          total_spent:::more_than:::300:::AND:::country:::matches:::GB
     */
    public function new(int $groupId, string $name, string $filter): array
    {
        $params = [
            'group_id' => $groupId,
            'name'     => $name,
            'filters'  => $filter,
        ];
        return $this->client->get('selections/new', $params);
    }
}
