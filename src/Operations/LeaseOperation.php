<?php

namespace SlaveMarket\Operations;

use SlaveMarket\Calculators\LeaseContractPriceCalculator;
use SlaveMarket\Entities\LeaseContract;
use SlaveMarket\Handlers\LeaseContractOverlapHandler;
use SlaveMarket\Handlers\TimeRounderHandler;
use SlaveMarket\Repositories\LeaseContractsRepositoryInterface;
use SlaveMarket\Repositories\SlavesRepositoryInterface;
use SlaveMarket\Responses\LeaseResponse;
use SlaveMarket\Repositories\MastersRepositoryInterface;
use SlaveMarket\Requests\LeaseRequest;
use SlaveMarket\Validators\LeaseOperationValidator;

/**
 * Операция "Арендовать раба"
 *
 * @package SlaveMarket\Lease
 */
class LeaseOperation
{
    /**
     * LeaseOperation constructor.
     *
     * @param LeaseContractsRepositoryInterface $contractsRepo
     * @param MastersRepositoryInterface $mastersRepo
     * @param SlavesRepositoryInterface $slavesRepo
     */
    public function __construct(
        protected LeaseContractsRepositoryInterface $contractsRepo,
        protected MastersRepositoryInterface $mastersRepo,
        protected SlavesRepositoryInterface $slavesRepo
    ) {
        $this->contractsRepository = $contractsRepo;
        $this->mastersRepository   = $mastersRepo;
        $this->slavesRepository    = $slavesRepo;
    }

    /**
     * Выполнить операцию
     *
     * @param LeaseRequest $request
     *
     * @return LeaseResponse
     */
    public function run(LeaseRequest $request): LeaseResponse
    {
        if (!$request->dateTimeFrom || !$request->dateTimeTo || !$request->slaveId || !$request->masterId) {
            return $this->returnResponse('Invalid request data: One of required fields not exists');
        }

        $requestMaster = $this->mastersRepository->getById($request->masterId);
        $requestSlave  = $this->slavesRepository->getById($request->slaveId);

        $validator = new LeaseOperationValidator($request, $requestMaster, $requestSlave);
        $validatorResponse = $validator->validate();

        if ($validatorResponse->errorMsg) {
            return $this->returnResponse($validatorResponse->errorMsg);
        }

        $requestDateFrom = \DateTime::createFromFormat($request::TIME_FORMAT, $request->dateTimeFrom);
        $requestDateTo   = \DateTime::createFromFormat($request::TIME_FORMAT, $request->dateTimeTo);

        $timeRounderHandler  = new TimeRounderHandler($requestDateFrom, $requestDateTo);
        $handleResponse      = $timeRounderHandler->handle();
        $requestFromDateTime = $handleResponse->roundedTimeFrom;
        $requestToDateTime   = $handleResponse->roundedTimeTo;

        // Беру по slave_id его массив расписаний
        $slaveActualContracts           = $this->contractsRepository->getActualBySlaveId($request->slaveId,
            $requestDateFrom, $requestDateTo);
        $contractsOverlapHandler
            = new LeaseContractOverlapHandler(
                $requestFromDateTime,
                $requestToDateTime,
                $request::TIME_FORMAT,
                $slaveActualContracts,
            );

        $contractOverlapHandlerResponse = $contractsOverlapHandler->handle();
        $intersectContracts             = $contractOverlapHandlerResponse->intersectLeaseContracts;

        $contractMasterIds = array_column($slaveActualContracts, 'master_id');
        $contractMasters   = $this->mastersRepository->getByIdList($contractMasterIds);

        foreach ($intersectContracts as $contract) {
            $contractMaster = $contractMasters[$contract->masterId];
            if ($contractMaster->VIPLevel >= $requestMaster->VIPLevel) {
                $this->returnResponse(
                    sprintf('Slave %s [#%s] cannot be rented because other master %s with higher Vip-level rent his from %s to %s',
                        $requestSlave->name, $requestSlave->id, $contractMaster->name,
                        $contract->dateTimeFrom, $contract->dateTimeTo
                    ),
                );
            }
        }

        $contractIds = array_column($intersectContracts, 'master_id');

        // Если мы можем арендовать рабат (создать договор), и есть пересечения с другими,
        // то аннулируем всех, кто пересёкся с клиентом из запроса
        $this->contractsRepository->deleteByIds($contractIds);

        $priceCalculator = new LeaseContractPriceCalculator($requestSlave, $requestMaster, $requestDateFrom,
            $requestDateTo);
        $totalPrice      = $priceCalculator->calculate();

        $leaseContract               = new LeaseContract();
        $leaseContract->slaveId      = $requestSlave->id;
        $leaseContract->masterId     = $requestMaster->id;
        $leaseContract->dateTimeFrom = $request->dateTimeFrom;
        $leaseContract->dateTimeTo   = $request->dateTimeTo;
        $leaseContract->totalPrice   = $totalPrice;

        $this->contractsRepository->create($leaseContract);

        return $this->returnResponse(null, $leaseContract);
    }

    private function returnResponse(string $errorMsg = null, LeaseContract $leaseContract = null)
    {
        $response = new LeaseResponse;
        if ($leaseContract) {
            $response->setLeaseContract($leaseContract);
        }

        if ($errorMsg) {
            $response->addError($errorMsg);
        }

        return $response;
    }
}