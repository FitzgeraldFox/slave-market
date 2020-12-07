<?php

namespace SlaveMarket\Entities;

class LeaseContract
{
    public int $id;
    public int $slaveId;
    public int $masterId;
    public string $dateTimeFrom;
    public string $dateTimeTo;
    public float $totalPrice;
    public bool $del;
}