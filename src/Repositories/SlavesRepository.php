<?php

namespace SlaveMarket\Repositories;

use SlaveMarket\Entities\Slave;

class SlavesRepository implements SlavesRepositoryInterface
{
    /**
     * @param int[] $ids
     *
     * @return Slave[]|array
     */
    public function getByIdList(array $ids): array
    {
        // TODO: Implement getByIdList() method.
    }

    /**
     * @param int $id
     *
     * @return Slave|null
     */
    public function getById(int $id): ?Slave
    {

    }
}