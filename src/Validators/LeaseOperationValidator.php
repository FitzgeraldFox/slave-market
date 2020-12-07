<?php

namespace SlaveMarket\Validators;

use SlaveMarket\Entities\Master;
use SlaveMarket\Entities\Slave;
use SlaveMarket\Handlers\TimeRounderHandler;
use SlaveMarket\Requests\LeaseRequest;
use SlaveMarket\Validators\Responses\BaseValidatorResponse;
use SlaveMarket\Validators\Responses\LeaseOperationValidatorResponse;

class LeaseOperationValidator implements ValidatorInterface
{
    public function __construct(
        private LeaseRequest $request,
        private ?Master $requestMaster = null,
        private ?Slave $requestSlave = null,
    ) {
    }

    public function validate(): BaseValidatorResponse
    {
        if (!$this->requestMaster) {
            return $this->makeResult('Master by id not found');
        }

        if (!$this->requestSlave) {
            return $this->makeResult('Slave by id not found');
        }

        $requestTimeFrom = \DateTime::createFromFormat($this->request::TIME_FORMAT, $this->request->dateTimeFrom);
        $requestTimeTo   = \DateTime::createFromFormat($this->request::TIME_FORMAT, $this->request->dateTimeTo);

        if (!(bool)$requestTimeFrom || !(bool)$requestTimeTo) {
            return $this->makeResult('Invalid request data: wrong time format');
        }

        $timeRounderHandler  = new TimeRounderHandler($requestTimeFrom, $requestTimeTo);
        $handlerResponse     = $timeRounderHandler->handle();
        $requestDateTimeFrom = $handlerResponse->roundedTimeFrom;
        $requestDateTimeTo   = $handlerResponse->roundedTimeTo;

        // Рабы не могут работать больше 16 часов в сутки при почасовой аренде
        $dateTimeInterval = $requestDateTimeFrom->diff($requestDateTimeTo);
        if ($dateTimeInterval->h > 16) {
            return $this->makeResult('Slave cannot be rented more than 16 hours');
        }

        // Рабочий день начинается с 00:00
        if (
            $requestDateTimeFrom < $requestDateTimeTo
            && $requestDateTimeFrom->format('d') < $requestDateTimeTo->format('d')
        ) {
            return $this->makeResult('You cannot hourly rent if timeFrom in last day and timeTo in next day because slave work day start at midnight');
        }

        return $this->makeResult();
    }

    protected function makeResult(string $errorMsg = null)
    {
        $response = new LeaseOperationValidatorResponse();

        if ($errorMsg) {
            $response->errorMsg = $errorMsg;
        }

        return $response;
    }
}