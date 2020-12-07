<?php

namespace SlaveMarket\Validators;

use SlaveMarket\Validators\Responses\BaseValidatorResponse;

interface ValidatorInterface
{
    public function validate(): BaseValidatorResponse;
}