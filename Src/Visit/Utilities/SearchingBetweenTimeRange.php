<?php

namespace TheClinic\Visit\Utilities;

use TheClinicDataStructures\DataStructures\Visit\DSVisits;
use TheClinic\Exceptions\Visit\VisitSearchFailure;
use TheClinicDataStructures\DataStructures\Visit\DSVisit;

class SearchingBetweenTimeRange
{
    private SearchBetweenTimestamps $searchBetweenTimestamps;

    private ValidateTimeRanges $validateTimeRanges;

    public function __construct(
        null|SearchBetweenTimestamps $searchBetweenTimestamps = null,
        null|ValidateTimeRanges $validateTimeRanges = null,
    ) {
        $this->searchBetweenTimestamps = $searchBetweenTimestamps ?: new SearchBetweenTimestamps;
        $this->validateTimeRanges = $validateTimeRanges ?: new ValidateTimeRanges;
    }

    public function search(int $firstTS, int $lastTS, int $consumingTime, DSVisits $futureVisits): int
    {
        $this->validateTimeRanges->checkConsumingTimeInTimeRange($firstTS, $lastTS, $consumingTime);

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

            return $previousBlock;
        }

        throw new VisitSearchFailure("Failed to find a visit in requested time range.", 500);
    }
}
