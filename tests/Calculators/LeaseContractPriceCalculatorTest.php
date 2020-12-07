<?php

namespace SlaveMarket\Calculators;

use PHPUnit\Framework\TestCase;
use SlaveMarket\Entities\Master;
use SlaveMarket\Entities\Slave;

class LeaseContractPriceCalculatorTest extends TestCase
{
    public function testCalculate_returnInt()
    {
        // Arrange
        $slave = new Slave();
        $slave->id = 1;
        $slave->name = 'Ben';
        $slave->pricePerHour = 10.5;

        $master = new Master();
        $master->id = 1;
        $master->name = 'Joe';
        $master->VIPLevel = 0;

        $dateFrom = new \DateTime('2020-10-01 00:00:00');
        $dateTo = new \DateTime('2020-10-02 05:00:00');
        $expected = 220.5;

        $calculator = new LeaseContractPriceCalculator($slave, $master, $dateFrom, $dateTo);

        // Act
        $actual = $calculator->calculate();

        // Assert
        $this->assertEquals($expected, $actual);
    }
}