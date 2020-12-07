<?php

namespace SlaveMarket\Validators;

use SlaveMarket\Entities\Master;
use SlaveMarket\Entities\Slave;
use SlaveMarket\Handlers\TimeRounderHandler;
use SlaveMarket\Repositories\MastersRepositoryInterface;
use SlaveMarket\Repositories\SlavesRepositoryInterface;
use SlaveMarket\Requests\LeaseRequest;
use SlaveMarket\Validators\Responses\LeaseOperationValidatorResponse;
use SlaveMarket\Validators\Responses\BaseValidatorResponse;

class LeaseOperationValidator implements ValidatorInterface
{
    private LeaseRequest $request;

    private MastersRepositoryInterface $mastersRepository;

    private SlavesRepositoryInterface $slavesRepository;

    public function __construct(
        LeaseRequest $request,
        MastersRepositoryInterface $mastersRepo,
        SlavesRepositoryInterface $slavesRepo,
    ) {
        $this->request           = $request;
        $this->mastersRepository = $mastersRepo;
        $this->slavesRepository  = $slavesRepo;
    }

    public function validate(): BaseValidatorResponse
    {
        if (!$this->request->dateTimeFrom || !$this->request->dateTimeTo || !$this->request->slaveId || !$this->request->masterId) {
            return $this->makeResult('Invalid request data: One of required fields not exists');
        }
        $requestMaster = $this->mastersRepository->getById($this->request->masterId);
        $requestSlave  = $this->slavesRepository->getById($this->request->slaveId);

        if (!$requestMaster) {
            return $this->makeResult(sprintf('Master by id=%s not found', $this->request->masterId));
        }

        if (!$requestSlave) {
            return $this->makeResult(sprintf('Slave by id=%s not found', $this->request->slaveId));
        }

        $requestTimeFrom = \DateTime::createFromFormat($this->request::TIME_FORMAT,
            $this->request->dateTimeFrom);
        $requestTimeTo   = \DateTime::createFromFormat($this->request::TIME_FORMAT,
            $this->request->dateTimeTo);

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

        return $this->makeResult(null, $requestMaster, $requestSlave);
    }

    protected function makeResult(string $errorMsg = null, ?Master $requestMaster = null, ?Slave $requestSlave = null)
    {
        $response = new LeaseOperationValidatorResponse();

        if ($errorMsg) {
            $response->errorMsg = $errorMsg;
        }

        if ($requestMaster) {
            $response->info['requestMaster'] = $requestMaster;
        }

        if ($requestSlave) {
            $response->info['requestSlave'] = $requestSlave;
        }

        return $response;
    }
}