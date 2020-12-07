<?php

namespace SlaveMarket\Repositories;

use SlaveMarket\Entities\Slave;

/**
 * Репозиторий рабов
 *
 * @package SlaveMarket
 */
interface SlavesRepositoryInterface
{
    /**
     * Возвращает рабов по их id
     *
     * @param int[] $ids
     * @return Slave[]
     */
    public function getByIdList(array $ids): array;

    /**
     * @param int $id
     *
     * @return Slave|null
     */
    public function getById(int $id): ?Slave;
}