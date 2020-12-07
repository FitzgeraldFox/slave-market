<?php

namespace SlaveMarket\Repositories;

use SlaveMarket\Entities\Master;

class MastersRepository implements MastersRepositoryInterface
{
    /**
     * Возвращает массив хозяев по их id
     *
     * @param int[] $ids
     * @return Master[]|array
     **/
    public function getByIdList(array $ids) : array
    {
        // TODO: Implement getByIdList() method.
        // expected: [masterId => Master, ...]
    }

    public function getById(int $id): ?Master
    {
        // TODO: Implement getById() method.
    }
}