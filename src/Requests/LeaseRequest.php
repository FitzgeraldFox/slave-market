<?php

namespace SlaveMarket\Requests;

/**
 * Запрос на аренду раба
 *
 * @package SlaveMarket\Lease
 */
class LeaseRequest
{
    const TIME_FORMAT = 'Y-m-d H:i:s';

    /** @var int id хозяина */
    public $masterId;

    /** @var int id раба */
    public $slaveId;

    /** @var string время начала работ Y-m-d H:i:s */
    public $dateTimeFrom;

    /** @var string время окончания работ Y-m-d H:i:s */
    public $dateTimeTo;
}