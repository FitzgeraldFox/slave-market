<?php

namespace SlaveMarket\Validators;

require_once __DIR__ . '/../../vendor/autoload.php';

use PHPUnit\Framework\TestCase;
use SlaveMarket\Entities\Master;
use SlaveMarket\Entities\Slave;
use SlaveMarket\Requests\LeaseRequest;
use SlaveMarket\Validators\Responses\LeaseOperationValidatorResponse;

/**
 * Class LeaseOperationValidatorTest
 * @package SlaveMarket\Validators
 * @group   unit
 */
class LeaseOperationValidatorTest extends TestCase
{
    /**
     * Не можем найти Master по master_id
     */
    public function testValidate_notRequestMaster_returnErrorMsg()
    {
        // Arrange
        $request               = new LeaseRequest();
        $request->slaveId      = 1;
        $request->dateTimeFrom = '2020-01-01 12:00:00';
        $request->dateTimeTo   = '2020-01-02 14:00:00';
        $validator             = new LeaseOperationValidator($request);
        $expected              = new LeaseOperationValidatorResponse;
        $expected->errorMsg    = 'Master by id not found';

        // Act
        $actual = $validator->validate();

        // Assert
        $this->assertEquals($expected, $actual);
    }

    /**
     * Не можем найти раба по slave_id
     */
    public function testValidate_notRequestSlave_returnErrorMsg()
    {
        // Arrange
        $request               = new LeaseRequest();
        $request->masterId     = 1;
        $request->dateTimeFrom = '2020-01-01 12:00:00';
        $request->dateTimeTo   = '2020-01-02 14:00:00';

        $master           = new Master();
        $master->id       = 1;
        $master->name     = 'name';
        $master->VIPLevel = 1;

        $validator          = new LeaseOperationValidator($request, $master);
        $expected           = new LeaseOperationValidatorResponse;
        $expected->errorMsg = 'Slave by id not found';

        // Act
        $actual = $validator->validate();

        // Assert
        $this->assertEquals($expected, $actual);
    }

    /**
     * Неправильный формат timeFrom или timeTo
     */
    public function testValidate_invalidTimeFormat_returnErrorMsg()
    {
        // Arrange
        $request               = new LeaseRequest();
        $request->masterId     = 1;
        $request->slaveId      = 1;
        $request->dateTimeFrom = '2020-01-01 12-00:00';
        $request->dateTimeTo   = '2020-01-02 14*00:00';

        $master           = new Master();
        $master->id       = 1;
        $master->name     = 'name';
        $master->VIPLevel = 1;

        $slave               = new Slave();
        $slave->id           = 1;
        $slave->name         = 'name';
        $slave->pricePerHour = 100;

        $validator = new LeaseOperationValidator($request, $master, $slave);

        $expected           = new LeaseOperationValidatorResponse;
        $expected->errorMsg = 'Invalid request data: wrong time format';

        // Act
        $actual = $validator->validate();

        // Assert
        $this->assertEquals($expected, $actual);
    }

    /**
     * Аренда на более, чем 16 часов (без учёта полных дней)
     */
    public function testValidate_moreThan16Hours_returnErrorMsg()
    {
        // Arrange
        $request               = new LeaseRequest();
        $request->masterId     = 1;
        $request->slaveId      = 1;
        $request->dateTimeFrom = '2020-01-01 00:00:00';
        $request->dateTimeTo   = '2020-01-01 17:00:00';

        $master           = new Master();
        $master->id       = 1;
        $master->name     = 'name';
        $master->VIPLevel = 1;

        $slave               = new Slave();
        $slave->id           = 1;
        $slave->name         = 'name';
        $slave->pricePerHour = 100;

        $validator          = new LeaseOperationValidator($request, $master, $slave);
        $expected           = new LeaseOperationValidatorResponse;
        $expected->errorMsg = 'Slave cannot be rented more than 16 hours';

        // Act
        $actual = $validator->validate();

        // Assert
        $this->assertEquals($expected, $actual);
    }

    /**
     * Рабочий день начинается с 00:00.
     * Поэтому нельзя арендовать раньше полуночи и заканчивать аренду после полуночи
     */
    public function testValidate_timeFromBeforeMidnightTimeToAfterMidnight_returnErrorMsg()
    {
        // Arrange
        $request               = new LeaseRequest();
        $request->masterId     = 1;
        $request->slaveId      = 1;
        $request->dateTimeFrom = '2020-01-01 23:00:00';
        $request->dateTimeTo   = '2020-01-02 02:00:00';

        $master           = new Master();
        $master->id       = 1;
        $master->name     = 'name';
        $master->VIPLevel = 1;

        $slave               = new Slave();
        $slave->id           = 1;
        $slave->name         = 'name';
        $slave->pricePerHour = 100;

        $validator          = new LeaseOperationValidator($request, $master, $slave);
        $expected           = new LeaseOperationValidatorResponse;
        $expected->errorMsg = 'You cannot hourly rent if timeFrom in last day and timeTo in next day because slave work day start at midnight';

        // Act
        $actual = $validator->validate();

        // Assert
        $this->assertEquals($expected, $actual);
    }

    public function testValidate_moreThan16HoursWithRounding_returnErrorMsg()
    {
        // Arrange
        $request               = new LeaseRequest();
        $request->masterId     = 1;
        $request->slaveId      = 1;
        $request->dateTimeFrom = '2020-01-01 00:30:00';
        $request->dateTimeTo   = '2020-01-01 16:30:00';

        $master           = new Master();
        $master->id       = 1;
        $master->name     = 'name';
        $master->VIPLevel = 1;

        $slave               = new Slave();
        $slave->id           = 1;
        $slave->name         = 'name';
        $slave->pricePerHour = 100;

        $validator          = new LeaseOperationValidator($request, $master, $slave);
        $expected           = new LeaseOperationValidatorResponse;
        $expected->errorMsg = 'Slave cannot be rented more than 16 hours';

        // Act
        $actual = $validator->validate();

        // Assert
        $this->assertEquals($expected, $actual);
    }

    /**
     * Если всё хорошо, то возвращаем ответ
     */
    public function testValidate_allRight_returnValidatorResult()
    {
        // Arrange
        $request               = new LeaseRequest();
        $request->masterId     = 1;
        $request->slaveId      = 1;
        $request->dateTimeFrom = '2020-01-01 00:00:00';
        $request->dateTimeTo   = '2020-01-01 14:00:00';

        $master           = new Master();
        $master->id       = 1;
        $master->name     = 'name';
        $master->VIPLevel = 1;

        $slave               = new Slave();
        $slave->id           = 1;
        $slave->name         = 'name';
        $slave->pricePerHour = 100;

        $validator = new LeaseOperationValidator($request, $master, $slave);
        $expected  = new LeaseOperationValidatorResponse;

        // Act
        $actual = $validator->validate();

        // Assert
        $this->assertEquals($expected, $actual);
    }
}