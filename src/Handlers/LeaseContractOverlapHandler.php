<?php

namespace SlaveMarket\Handlers;

use Spatie\Period\Period;

class LeaseContractOverlapHandler implements HandlerInterface
{
    public function __construct(
        protected \DateTime $requestFromDateTime,
        protected \DateTime $requestToDateTime,
        protected string $timeFormat,
        protected array $slaveActualContracts,
    )
    {}

    public function handle(): LeaseContractsOverlapHandlerResponse
    {
        $timeFrom = new \DateTimeImmutable($this->requestFromDateTime->format($this->timeFormat));
        $timeTo   = new \DateTimeImmutable($this->requestToDateTime->format($this->timeFormat));

        $requestTimePeriod = new Period($timeFrom, $timeTo);

        $intersectContracts = [];
        foreach ($this->slaveActualContracts as $contract) {
            $contractDateTimeFrom = \DateTimeImmutable::createFromFormat($this->timeFormat, $contract->dateTimeFrom);
            $contractDateTimeTo   = \DateTimeImmutable::createFromFormat($this->timeFormat, $contract->dateTimeTo);
            $contractTimePeriod = new Period($contractDateTimeFrom, $contractDateTimeTo);
            if ($contractTimePeriod->overlapsWith($requestTimePeriod)) {
                $intersectContracts[] = $contract;
            }
        }

        return $this->makeResponse($intersectContracts);
    }

    /**
     * @param array $intersectContracts
     * @param array $errors
     *
     * @return LeaseContractsOverlapHandlerResponse
     */
    protected function makeResponse(array $intersectContracts = [], array $errors = []): LeaseContractsOverlapHandlerResponse
    {
        $response                          = new LeaseContractsOverlapHandlerResponse;
        $response->intersectLeaseContracts = $intersectContracts;
        $response->errors                  = $errors;

        return $response;
    }
}