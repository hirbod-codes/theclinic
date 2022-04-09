<?php

namespace TheClinic\Visit\Utilities;

use TheClinicDataStructures\DataStructures\Visit\DSVisits;
use TheClinic\Exceptions\Visit\NeededTimeOutOfRange;
use TheClinic\Exceptions\Visit\VisitSearchFailure;
use TheClinicDataStructures\DataStructures\Visit\DSVisit;

class SearchingBetweenTimeRange
{
    private SearchBetweenTimestamps $searchBetweenTimestamps;

    public function __construct(
        null|SearchBetweenTimestamps $searchBetweenTimestamps = null,
    ) {
        $this->searchBetweenTimestamps = $searchBetweenTimestamps ?: new SearchBetweenTimestamps;
    }

    public function search(int $firstTS, int $lastTS, int $consumingTime, DSVisits $futureVisits): int
    {
        // For testing purposes
        $firsDT = (new \DateTime)->setTimestamp($firstTS);
        $lastDT = (new \DateTime)->setTimestamp($lastTS);

        $this->validateTimeRange($firstTS, $lastTS, $consumingTime);

        foreach ($this->searchBetweenTimestamps->search(
            $firstTS,
            $lastTS,
            $consumingTime,
            $futureVisits,
            function (DSVisit $visit): int {
                return $visit->getVisitTimestamp();
            },
            function (DSVisit $visit): int {
                return $visit->getVisitTimestamp() + $visit->getConsumingTime();
            }
        ) as $array) {
            $previousBlock = $array[0];
            $currentBlock = $array[1];

            // For testing purposes
            $previousBlockDT = (new \DateTime)->setTimestamp($previousBlock);
            $currentBlockDT = (new \DateTime)->setTimestamp($currentBlock);

            return $previousBlock;
        }

        throw new VisitSearchFailure("Failed to find a visit in requested time range.", 500);
    }

    private function validateTimeRange(int $firstTS, int $lastTS, int $consumingTime): void
    {
        if ($firstTS >= $lastTS) {
            throw new VisitSearchFailure("Failed to find a visit in requested time range.", 500);
        }
        if (($lastTS - $firstTS) < $consumingTime) {
            throw new NeededTimeOutOfRange("Incorrect time range(" . strval($firstTS - $lastTS) . ") for requested time consumption(" . strval($consumingTime) . ".)", 500);
        }
    }
}
