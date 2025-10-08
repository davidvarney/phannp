<?php

namespace Phannp\Resources;

use Phannp\Client;

abstract class Resource
{
    protected Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }
}
