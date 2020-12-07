<?php

namespace SlaveMarket\Entities;

/**
 * Раб (Бедняга :-()
 *
 * @package SlaveMarket
 */
class Slave
{
    /** @var int id раба */
    public $id;

    /** @var string имя раба */
    public $name;

    /** @var float Стоимость раба за час работы */
    public $pricePerHour;
}