<?php

namespace SlaveMarket\Repositories;

use SlaveMarket\Entities\Master;

/**
 * Репозиторий хозяев
 *
 * @package SlaveMarket\Repositories
 */
interface MastersRepositoryInterface
{
    /**
     * Возвращает массив хозяев по их id
     *
     * @param int[] $ids
     * @return Master[]|array
     */
    public function getByIdList(array $ids) : array;

    /**
     * @param int $id
     *
     * @return Master|null
     */
    public function getById(int $id): ?Master;
}