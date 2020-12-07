<?php

namespace SlaveMarket\Handlers;

require_once __DIR__ . '/../../vendor/autoload.php';

use PHPUnit\Framework\TestCase;

/**
 * Class TimeRounderHandlerTest
 * @package SlaveMarket\Handlers
 * @group unit
 */
class TimeRounderHandlerTest extends TestCase
{
    public function testHandle_timeFromWithMinutesTimeToWithMinutes_returnHandlerResponse()
    {
        // Arrange
        $handler  = new TimeRounderHandler(new \DateTime('2020-11-01 12:30:00'), new \DateTime('2020-11-01 14:30:00'));
        $expected = new TimeRounderHandlerResponse();
        $expected->roundedTimeFrom = new \DateTime('2020-11-01 12:00:00');
        $expected->roundedTimeTo   = new \DateTime('2020-11-01 15:00:00');
        $expected->errors = [];

        // Act
        $actual = $handler->handle();

        // Assert
        $this->assertEquals($expected, $actual);
    }
}