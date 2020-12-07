<?php

namespace SlaveMarket\Validators\Responses;

use SlaveMarket\Entities\Master;
use SlaveMarket\Entities\Slave;

class LeaseOperationValidatorResponse extends BaseValidatorResponse
{
    public ?Master $requestMaster;
    public ?Slave  $requestSlave;
}