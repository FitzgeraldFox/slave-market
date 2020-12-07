<?php

namespace SlaveMarket\Operations;

use PHPUnit\Framework\TestCase;
use SlaveMarket\Entities\LeaseContract;
use SlaveMarket\Entities\Master;
use SlaveMarket\Repositories\LeaseContractRepository;
use SlaveMarket\Repositories\MastersRepository;
use SlaveMarket\Repositories\SlavesRepository;
use SlaveMarket\Requests\LeaseRequest;
use SlaveMarket\Responses\LeaseResponse;

/**
 * Class LeaseOperationTest
 * @package SlaveMarket\Operations
 * @group unit
 */
class LeaseOperationTest extends TestCase
{
    public function testRun_wrongRequestData_returnLeaseResponseWithError()
    {
        // Arrange
        $contractsRepo = new LeaseContractRepository();
        $mastersRepo = new MastersRepository();
        $slavesRepo = new SlavesRepository();

        $operation = new LeaseOperation($contractsRepo, $mastersRepo, $slavesRepo);
        $request = new LeaseRequest();

        $expected = new LeaseResponse;
        $expected->addError('Invalid request data: One of required fields not exists');

        // Act
        $actual = $operation->run($request);

        // Assert
        $this->assertEquals($expected, $actual);
    }

//    public function testRun_cannotBeRented_returnLeaseResponseWithError()
//    {
//        // Arrange
//        $contracts = [];
//
//        $contract = new LeaseContract();
//        $contract->id = 1;
//        $contract->slaveId = 1;
//        $contract->masterId = 1;
//        $contract->dateTimeFrom = '2020-10-01 00:00:00';
//        $contract->dateTimeTo = '2020-10-01 02:00:00';
//        $contract->totalPrice = 10.5;
//
//        $contracts[] = $contract;
//
//        $contractsRepo = $this->makeFakeContractsRepository($contracts);
//
//        $mastersRepo = new MastersRepository();
//        $slavesRepo = new SlavesRepository();
//
//        $operation = new LeaseOperation($contractsRepo, $mastersRepo, $slavesRepo);
//        $request = new LeaseRequest();
//
//        $expected = new LeaseResponse;
//        $expected->addError('Slave %s [#%s] cannot be rented because other master %s with higher Vip-level rent his from %s to %s');
//
//        // Act
//        $actual = $operation->run($request);
//
//        // Assert
//        $this->assertEquals($expected, $actual);
//    }

    /**
     * Stub репозитория контрактов
     *
     * @param LeaseContract[] ...$contracts
     * @return LeaseContractRepository
     */
    private function makeFakeContractsRepository(...$contracts): LeaseContractRepository
    {
        $contractsRepository = $this->prophesize(LeaseContractRepository::class);

        /** @var LeaseContract $contract */
        foreach ($contracts as $contract) {
            $contractsRepository->getActualBySlaveId($contract->id)->willReturn([$contract]);
        }

        return $contractsRepository->reveal();
    }

    /**
     * Stub репозитория хозяев
     *
     * @param Master[] ...$masters
     * @return MastersRepository
     */
    private function makeFakeMastersRepository(...$masters): MastersRepository
    {
        $mastersRepository = $this->prophesize(MastersRepository::class);

        $masterIds = array_column($masters, 'id');
        $mastersRepository->getByIdList($masterIds)->willReturn($masters);

        /** @var Master $master */
        foreach ($masters as $master) {
            $mastersRepository->getById($master->id)->willReturn([$master]);
        }

        return $mastersRepository->reveal();
    }
}