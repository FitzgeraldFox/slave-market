<?php

namespace SlaveMarket\Repositories;

use SlaveMarket\Entities\LeaseContract;

class LeaseContractRepository implements LeaseContractsRepositoryInterface
{
    /**
     * @param int $slaveId
     * @param string $dateFrom
     * @param string $dateTo
     *
     * @return LeaseContract[]
     */
    public function getActualBySlaveId(int $slaveId, string $dateFrom, string $dateTo): array
    {}

    public function deleteByIds(array $contractIds): void
    {}

    public function create(LeaseContract $leaseContract): void
    {}
}