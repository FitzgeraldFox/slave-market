<?php

namespace SlaveMarket\Calculators;

use SlaveMarket\Entities\Master;
use SlaveMarket\Entities\Slave;

class LeaseContractPriceCalculator implements CalculatorInterface
{
    public function __construct(
        protected Slave $slave,
        protected Master $master,
        protected \DateTime $dateTimeFrom,
        protected \DateTime $dateTimeTo,
    )
    {}

    public function calculate()
    {
        // Берём почасовую ставку раба
        $slaveHourlyPrice = $this->slave->pricePerHour;

        $dateInterval = $this->dateTimeFrom->diff($this->dateTimeTo);
        $rentedHoursCount = ($dateInterval->d * 16) + $dateInterval->h;

        return $rentedHoursCount * $slaveHourlyPrice;
    }
}