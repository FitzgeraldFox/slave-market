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
     * @param \DateTime $dateFrom Y-m-d
     * @param \DateTime $dateTo Y-m-d
     * @return LeaseContract[]|array
     */
    public function getActualBySlaveId(int $slaveId, \DateTime $dateFrom, \DateTime $dateTo) : array;

    public function deleteByIds(array $contractIds): void;

    public function create(LeaseContract $leaseContract): void;
}