<?php

namespace SlaveMarket\Repositories;

use SlaveMarket\Entities\LeaseContract;

class LeaseContractRepository implements LeaseContractsRepositoryInterface
{
    /**
     * @param int $slaveId
     * @param \DateTime $dateFrom
     * @param \DateTime $dateTo
     *
     * @return LeaseContract[]|array
     */
    public function getActualBySlaveId(int $slaveId, \DateTime $dateFrom, \DateTime $dateTo): array
    {}

    public function deleteByIds(array $contractIds): void
    {}

    public function create(LeaseContract $leaseContract): void
    {}
}