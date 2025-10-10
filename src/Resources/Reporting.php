<?php

namespace Phannp\Resources;

class Reporting extends Resource
{
    /**
     * Retrieves a status summary on individual items within a date range.
     * Use a start date and end date in the following format: YYYY-MM-DD.
     * The end date will include everything on that day.
     * 
     * @link https://www.stannp.com/us/direct-mail-api/reporting#summary
     *
     * @param string $startdate	      The start date for the summary in the format YYYY-MM-DD.
     * @param string $enddate	      The end date for the summary in the format YYYY-MM
     * @param string $received	      We have received the request.
     * @param string $producing	      We are printing or finishing the mailpiece.
     * @param string $handed_over	  We have handed the mailpiece over to the delivery service (FedEx, UPS, etc.).
     * @param string $local_delivery  The item is at the final delivery office and will be delivered within 24 hours.
     * @param string $delivered	      We estimate the item has been successfully delivered.
     * @param string $returned	      We estimate the item has been successfully delivered.
     * @param string $cancelled	      The item has been cancelled before it was produced and posted.
     */
    public function summary(
        ?string $startdate = null,
        ?string $enddate = null,
        string $received = '',
        string $producing = '',
        string $handed_over = '',
        string $local_delivery = '',
        string $delivered = '',
        string $returned = '',
        string $cancelled = ''
    ): array {
        $startdate = $startdate ?? date('Y-m-d', strtotime('-1 month'));
        $enddate = $enddate ?? date('Y-m-d');

        return $this->client->get(
            "reporting/summary/".$startdate."/".$enddate,
            [
                'received'       => $received,
                'producing'      => $producing,
                'handed_over'    => $handed_over,
                'local_delivery' => $local_delivery,
                'delivered'      => $delivered,
                'returned'       => $returned,
                'cancelled'      => $cancelled,
            ]
        );
    }

    /**
     * Retrieves a list of mailpiece objects sent within the specified date range.
     * Status and tag filters are optional.
     * 
     * @link https://www.stannp.com/us/direct-mail-api/reporting#list
     *
     * @param string $startdate mandatory Start date of the reporting period (YYYY-MM-DD).
     * @param string $enddate   mandatory End date of the reporting period (YYYY-MM-DD).
     * @param string $status    optional  Status filter (e.g., delivered, returned, etc.).
     * @param string $tag       optional  Tag filter for searching by custom tags.
     */
    public function list(
        ?string $startdate = null,
        ?string $enddate = null,
        string $status = '',
        string $tags = ''
    ): array {
        return $this->client->get("reporting/list/".$startdate."/".$enddate."/".$status."/".$tags);
    }
}
