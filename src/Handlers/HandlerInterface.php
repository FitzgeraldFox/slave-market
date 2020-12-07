<?php

namespace SlaveMarket\Handlers;

interface HandlerInterface
{
    public function handle(): BaseHandlerResponse;
}