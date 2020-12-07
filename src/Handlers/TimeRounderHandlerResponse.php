<?php

namespace SlaveMarket\Handlers;

class TimeRounderHandlerResponse extends BaseHandlerResponse
{
    public ?\DateTime $roundedTimeFrom = null;
    public ?\DateTime $roundedTimeTo = null;
}