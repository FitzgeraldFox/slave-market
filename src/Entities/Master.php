<?php

namespace SlaveMarket\Entities;

/**
 * Хозяин
 *
 * @package SlaveMarket
 */
class Master
{
    /** @var int id хозяина */
    public $id;

    /** @var string имя хозяина */
    public $name;

    /** @var int уровень VIP-клиента */
    public $VIPLevel;
}