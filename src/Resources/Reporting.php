<?php

namespace Phannp\Resources;

class Reporting extends Resource
{
    public function getStats(array $params = []): array
    {
        return $this->client->get('reporting/stats', $params);
    }

    public function getCampaignStats(int $campaignId): array
    {
        return $this->client->get("reporting/campaign/{$campaignId}");
    }
}
