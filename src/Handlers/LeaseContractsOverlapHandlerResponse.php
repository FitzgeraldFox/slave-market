<?php

namespace SlaveMarket\Handlers;

use SlaveMarket\Entities\LeaseContract;

class LeaseContractsOverlapHandlerResponse extends BaseHandlerResponse
{
    /**
     * @var LeaseContract[]
     */
    public array $intersectLeaseContracts = [];
}