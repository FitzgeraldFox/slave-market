<?php

namespace SlaveMarket\Operations;

use SlaveMarket\Calculators\LeaseContractPriceCalculator;
use SlaveMarket\Entities\LeaseContract;
use SlaveMarket\Entities\Master;
use SlaveMarket\Entities\Slave;
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
        $requestMaster = $this->mastersRepository->getById($request->masterId);
        $requestSlave  = $this->slavesRepository->getById($request->slaveId);

        $this->validate($request, $requestMaster, $requestSlave);

        $requestDateFrom = \DateTime::createFromFormat($request::TIME_FORMAT, $request->dateTimeFrom);
        $requestDateTo   = \DateTime::createFromFormat($request::TIME_FORMAT, $request->dateTimeTo);

        $timeRounderHandler  = new TimeRounderHandler($requestDateFrom, $requestDateTo);
        $handleResponse      = $timeRounderHandler->handle();
        $requestFromDateTime = $handleResponse->roundedTimeFrom;
        $requestToDateTime   = $handleResponse->roundedTimeTo;

        // Беру по slave_id массив контрактов
        $slaveActualContracts = $this->getSlaveActualContracts($request, $requestFromDateTime, $requestToDateTime);
        $intersectContracts   = $this->getIntersectContracts($request, $requestFromDateTime, $requestToDateTime, $slaveActualContracts);
        $contractMasters      = $this->getContractMasters($slaveActualContracts);

        $this->checkContractIntersectsWithExisting($intersectContracts, $contractMasters, $requestMaster, $requestSlave);

        // Если мы можем арендовать раба (создать договор), и есть пересечения с другими,
        // то аннулируем всех, кто пересёкся с клиентом из запроса
        $this->deleteIntersectedContracts($intersectContracts);

        $totalPrice = $this->calculateContractTotalPrice($requestSlave, $requestMaster, $requestDateFrom, $requestDateTo);

        $leaseContract               = new LeaseContract();
        $leaseContract->slaveId      = $requestSlave->id;
        $leaseContract->masterId     = $requestMaster->id;
        $leaseContract->dateTimeFrom = $request->dateTimeFrom;
        $leaseContract->dateTimeTo   = $request->dateTimeTo;
        $leaseContract->totalPrice   = $totalPrice;

        $this->contractsRepository->create($leaseContract);

        return $this->returnResponse(null, $leaseContract);
    }

    protected function calculateContractTotalPrice(Slave $requestSlave, Master $requestMaster, \DateTime $requestDateFrom,
        \DateTime $requestDateTo)
    {
        $priceCalculator = new LeaseContractPriceCalculator($requestSlave, $requestMaster, $requestDateFrom,
            $requestDateTo);
        return $priceCalculator->calculate();
    }

    protected function deleteIntersectedContracts(array $intersectContracts)
    {
        $contractIds = array_column($intersectContracts, 'master_id');
        $this->contractsRepository->deleteByIds($contractIds);
    }

    protected function getContractMasters(array $slaveActualContracts)
    {
        $contractMasterIds = array_column($slaveActualContracts, 'master_id');
        return $this->mastersRepository->getByIdList($contractMasterIds);
    }

    /**
     * @param LeaseRequest $request
     * @param \DateTime $requestDateFrom
     * @param \DateTime $requestDateTo
     *
     * @return LeaseContract[]|array
     */
    protected function getSlaveActualContracts(LeaseRequest $request, \DateTime $requestDateFrom, \DateTime $requestDateTo) : array
    {
        return $this->contractsRepository->getActualBySlaveId($request->slaveId,
            $requestDateFrom, $requestDateTo);
    }

    protected function checkContractIntersectsWithExisting($intersectContracts, $contractMasters, Master $requestMaster, Slave $requestSlave)
    {
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

        return false;
    }

    /**
     * @param LeaseRequest $request
     * @param \DateTime $requestDateFrom
     * @param \DateTime $requestDateTo
     * @param $slaveActualContracts
     *
     * @return LeaseContract[]
     */
    protected function getIntersectContracts(
        LeaseRequest $request,
        \DateTime $requestDateFrom,
        \DateTime $requestDateTo,
        array $slaveActualContracts
    ) {
        $contractsOverlapHandler
            = new LeaseContractOverlapHandler(
            $requestDateFrom,
            $requestDateTo,
            $request::TIME_FORMAT,
            $slaveActualContracts,
        );

        $contractOverlapHandlerResponse = $contractsOverlapHandler->handle();
        return $contractOverlapHandlerResponse->intersectLeaseContracts;
    }

    /**
     * @param LeaseRequest $request
     * @param Master $requestMaster
     * @param Slave $requestSlave
     *
     * @return LeaseResponse
     */
    protected function validate(LeaseRequest $request, Master $requestMaster, Slave $requestSlave)
    {
        if (!$request->dateTimeFrom || !$request->dateTimeTo || !$request->slaveId || !$request->masterId) {
            return $this->returnResponse('Invalid request data: One of required fields not exists');
        }

        $validator = new LeaseOperationValidator($request, $requestMaster, $requestSlave);
        $validatorResponse = $validator->validate();

        if ($validatorResponse->errorMsg) {
            return $this->returnResponse($validatorResponse->errorMsg);
        }
    }

    /**
     * @param string|null $errorMsg
     * @param LeaseContract|null $leaseContract
     *
     * @return LeaseResponse
     */
    protected function returnResponse(string $errorMsg = null, LeaseContract $leaseContract = null)
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