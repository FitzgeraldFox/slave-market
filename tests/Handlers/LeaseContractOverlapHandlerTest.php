<?php

namespace SlaveMarket\Handlers;

require_once __DIR__ . '/../../vendor/autoload.php';

use PHPUnit\Framework\TestCase;
use SlaveMarket\Entities\LeaseContract;

class LeaseContractOverlapHandlerTest extends TestCase
{
    public function testHandle_slaveActualScheduleListHasIntersects_returnResponseWithIntersectedScheduleHours()
    {
        // Arrange
        $requestFromDateTime = new \DateTime('2020-10-01 00:00:00');
        $requestToDateTime   = new \DateTime('2020-10-01 15:00:00');
        $timeFormat = 'Y-m-d H:i:s';

        $slaveActualScheduleList = [];

        $slaveSchedule = new LeaseContract;
        $slaveSchedule->dateTimeFrom = '2020-10-01 00:00:00';
        $slaveSchedule->dateTimeTo = '2020-10-01 02:00:00';

        $slaveActualScheduleList[] = $slaveSchedule;

        $slaveSchedule2 = new LeaseContract;
        $slaveSchedule2->dateTimeFrom = '2020-10-01 03:00:00';
        $slaveSchedule2->dateTimeTo = '2020-10-01 04:00:00';

        $slaveActualScheduleList[] = $slaveSchedule2;

        $slaveSchedule3 = new LeaseContract;
        $slaveSchedule3->dateTimeFrom = '2020-10-02 03:00:00';
        $slaveSchedule3->dateTimeTo = '2020-10-02 04:00:00';

        $slaveActualScheduleList[] = $slaveSchedule3;

        $handler = new LeaseContractOverlapHandler(
            $requestFromDateTime,
            $requestToDateTime,
            $timeFormat,
            $slaveActualScheduleList
        );

        $expected                          = new LeaseContractsOverlapHandlerResponse;
        $expected->intersectLeaseContracts = [$slaveSchedule, $slaveSchedule2];

        // Act
        $actual = $handler->handle();

        // Assert
        $this->assertEquals($expected, $actual);
    }
}