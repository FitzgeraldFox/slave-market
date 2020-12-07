<?php

namespace SlaveMarket\Handlers;

class TimeRounderHandler implements HandlerInterface
{
    public function __construct(public \DateTime $timeFrom, public \DateTime $timeTo)
    {
    }

    public function handle(): TimeRounderHandlerResponse
    {
        if ((int)$this->timeFrom->format('i') > 0) {
            $this->timeFrom->modify('-' . (int)$this->timeFrom->format('i') . ' minutes');
        }

        if ((int)$this->timeTo->format('i') > 0) {
            $this->timeTo->modify('+1 hour');
            $this->timeTo->modify('-' . (int)$this->timeTo->format('i') . ' minutes');
        }

        return $this->makeResponse($this->timeFrom, $this->timeTo);
    }

    protected function makeResponse(
        ?\DateTime $requestFromDateTime = null,
        ?\DateTime $requestToDateTime = null,
        $errors = []
    )
    {
        $response = new TimeRounderHandlerResponse;
        $response->roundedTimeFrom = $requestFromDateTime;
        $response->roundedTimeTo   = $requestToDateTime;
        $response->errors = $errors;

        return $response;
    }
}