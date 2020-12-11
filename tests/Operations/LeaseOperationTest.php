<?php

namespace SlaveMarket\Operations;

use PHPUnit\Framework\TestCase;
use SlaveMarket\Entities\LeaseContract;
use SlaveMarket\Entities\Master;
use SlaveMarket\Entities\Slave;
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

    public function testRun_cannotBeRented_returnLeaseResponseWithError()
    {
        // Arrange
        $contract = new LeaseContract();
        $contract->id = 1;
        $contract->slaveId = 1;
        $contract->masterId = 1;
        $contract->dateTimeFrom = '2020-10-01 00:00:00';
        $contract->dateTimeTo = '2020-10-01 02:00:00';
        $contract->totalPrice = 10.5;

        $contractsRepo = $this->makeFakeContractsRepository([$contract]);

        $master = new Master;
        $master->id = 1;
        $master->name = 'Big Jack';
        $master->VIPLevel = 2;

        $masters[] = $master;

        $master = new Master;
        $master->id = 2;
        $master->name = 'Big Bob';
        $master->VIPLevel = 1;

        $masters[] = $master;

        $mastersRepo = $this->makeFakeMastersRepository($masters);

        $slave = new Slave;
        $slave->id = 1;
        $slave->name = 'Small Jerry';
        $slave->pricePerHour = 100.5;

        $slaves[] = $slave;

        $slavesRepo = $this->makeFakeSlavesRepository($slaves);

        $operation = new LeaseOperation($contractsRepo, $mastersRepo, $slavesRepo);

        $request = new LeaseRequest();
        $request->masterId = 2;
        $request->slaveId = 1;
        $request->dateTimeFrom = '2020-10-01 01:30:00';
        $request->dateTimeTo = '2020-10-01 03:30:00';

        $expected = new LeaseResponse;
        $expected->addError('Slave Small Jerry [#1] cannot be rented because other master Big Jack with higher Vip-level rent his from 2020-10-01 00:00:00 to 2020-10-01 02:00:00');

        // Act
        $actual = $operation->run($request);

        // Assert
        $this->assertEquals($expected, $actual);
    }

    /**
     * Stub репозитория контрактов
     *
     * @param LeaseContract[] $contracts
     * @param int|null $slaveId
     *
     * @return LeaseContractRepository
     */
    private function makeFakeContractsRepository(array $contracts, int $slaveId = null): LeaseContractRepository
    {
        $contractsRepository = $this->prophesize(LeaseContractRepository::class);

        /** @var LeaseContract $contract */
        foreach ($contracts as $contract) {
            $contractsRepository->getActualBySlaveId($contract->slaveId)->willReturn([$contract]);
        }

        if (!$contracts) {
            $contractsRepository->getActualBySlaveId($slaveId)->willReturn([]);
        }

        return $contractsRepository->reveal();
    }

    /**
     * Stub репозитория хозяев
     *
     * @param Master[] $masters
     * @return MastersRepository
     */
    private function makeFakeMastersRepository(array $masters): MastersRepository
    {
        $mastersRepository = $this->prophesize(MastersRepository::class);

        $masterIds = array_column($masters, 'id');
        $mastersRepository->getByIdList($masterIds)->willReturn($masters);

        /** @var Master $master */
        foreach ($masters as $master) {
            $mastersRepository->getById($master->id)->willReturn($master);
        }

        return $mastersRepository->reveal();
    }

    /**
     * Stub репозитория рабов
     *
     * @param Slave[] $slaves
     * @return SlavesRepository
     */
    private function makeFakeSlavesRepository(array $slaves): SlavesRepository
    {
        $slavesRepository = $this->prophesize(SlavesRepository::class);

        $slaveIds = array_column($slaves, 'id');
        $slavesRepository->getByIdList($slaveIds)->willReturn($slaves);

        /** @var Slave $slave */
        foreach ($slaves as $slave) {
            $slavesRepository->getById($slave->id)->willReturn($slave);
        }

        return $slavesRepository->reveal();
    }
}