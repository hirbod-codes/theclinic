<?php

namespace TheClinic\Visit\Utilities;

use TheClinicDataStructures\DataStructures\Time\DSDownTimes;
use TheClinicDataStructures\DataStructures\Visit\DSVisits;
use TheClinic\Exceptions\Visit\NeededTimeOutOfRange;
use TheClinic\Exceptions\Visit\VisitSearchFailure;
use TheClinicDataStructures\DataStructures\Time\DSDownTime;

class SearchingBetweenDownTimes
{
    private SearchingBetweenTimeRange $searchingBetweenTimeRange;

    private SearchBetweenTimestamps $searchBetweenTimestamps;

    private DownTime $downTime;

    public function __construct(
        null|SearchBetweenTimestamps $searchBetweenTimestamps = null,
        null|SearchingBetweenTimeRange $searchingBetweenTimeRange = null,
        null|DownTime $downTime = null
    ) {
        $this->searchBetweenTimestamps = $searchBetweenTimestamps ?: new SearchBetweenTimestamps;
        $this->searchingBetweenTimeRange = $searchingBetweenTimeRange ?: new SearchingBetweenTimeRange;
        $this->downTime = $downTime ?: new DownTime;
    }

    public function search(int $firstTS, int $lastTS, DSVisits $futureVisits, DSDownTimes $dsDownTimes, int $consumingTime): int
    {
        // For testing purposes
        $firstDT = (new \DateTime)->setTimestamp($firstTS);
        $lastDT = (new \DateTime)->setTimestamp($lastTS);

        $this->validateTimeRange($firstTS, $lastTS, $consumingTime);

        $intruptingDSDownTimes = $this->downTime->findDownTimeIntruptionWithTimeRange($firstTS, $lastTS, $dsDownTimes);

        foreach ($this->searchBetweenTimestamps->search(
            $firstTS,
            $lastTS,
            $consumingTime,
            $intruptingDSDownTimes->cloneIt(),
            function (DSDownTime $dsDownTime): int {
                return $dsDownTime->getStartTimestamp();
            },
            function (DSDownTime $dsDownTime): int {
                return $dsDownTime->getEndTimestamp();
            }
        ) as $array) {
            $previousBlock = $array[0];
            $currentBlock = $array[1];

            // For testing purposes
            $previousBlockDT = (new \DateTime)->setTimestamp($previousBlock);
            $currentBlockDT = (new \DateTime)->setTimestamp($currentBlock);

            try {
                return $this->searchingBetweenTimeRange->search($previousBlock, $currentBlock, $consumingTime, $futureVisits);
            } catch (VisitSearchFailure $th) {
            } catch (NeededTimeOutOfRange $th) {
            }
        }

        throw new VisitSearchFailure("Failed to find a visit in the requested time range.", 500);
    }

    private function validateTimeRange(int $firstTS, int $lastTS, int $consumingTime): void
    {
        if ($firstTS >= $lastTS) {
            throw new VisitSearchFailure("Failed to find a visit in requested time range.", 500);
        }
        if (($lastTS - $firstTS) < $consumingTime) {
            throw new NeededTimeOutOfRange();
        }
    }
}
