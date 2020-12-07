<?php

namespace SlaveMarket\Repositories;

use SlaveMarket\Entities\LeaseContract;

/**
 * Репозиторий договоров аренды
 *
 * @package SlaveMarket\Repositories
 */
interface LeaseContractsRepositoryInterface
{
    /**
     * Возвращает список договоров аренды для раба, в которых заняты часы из указанного периода
     *
     * @param int $slaveId
     * @param string $dateFrom Y-m-d
     * @param string $dateTo Y-m-d
     * @return LeaseContract[]
     */
    public function getActualBySlaveId(int $slaveId, string $dateFrom, string $dateTo) : array;

    public function deleteByIds(array $contractIds): void;

    public function create(LeaseContract $leaseContract): void;
}