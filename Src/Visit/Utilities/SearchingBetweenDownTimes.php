<?php

namespace TheClinic\Visit\Utilities;

use TheClinicDataStructures\DataStructures\Time\DSDownTimes;
use TheClinicDataStructures\DataStructures\Visit\DSVisits;
use TheClinic\Exceptions\Visit\NeededTimeOutOfRange;
use TheClinic\Exceptions\Visit\VisitSearchFailure;
use TheClinicDataStructures\DataStructures\Time\DSDownTime;

class SearchingBetweenDownTimes
{
    private SearchingBetweenTimeRange $SearchingBetweenTimeRange;

    private DownTime $dowTime;

    public function __construct(SearchingBetweenTimeRange $SearchingBetweenTimeRange, DownTime $dowTime)
    {
        $this->SearchingBetweenTimeRange = $SearchingBetweenTimeRange;
        $this->dowTime = $dowTime;
    }

    public function search(int $firstTS, int $lastTS, DSVisits $futureVisits, DSDownTimes $dsDownTimes, int $consumingTime): int
    {
        // For testing purposes
        $firstDT = (new \DateTime)->setTimestamp($firstTS);
        $lastDT = (new \DateTime)->setTimestamp($lastTS);

        $this->validateTimeRange($firstTS, $lastTS, $consumingTime);

        $intruptingDSDownTimes = $this->dowTime->findDownTimeIntruptionWithTimeRange($firstTS, $lastTS, $dsDownTimes);

        if (count($intruptingDSDownTimes) === 0) {
            return $this->SearchingBetweenTimeRange->search($firstTS, $lastTS, $consumingTime, $futureVisits);
        } else {
            $newDSDownTimes = $intruptingDSDownTimes->cloneIt();

            /** @var \TheClinicDataStructures\DataStructures\Time\DSDownTime $dsDownTime */
            foreach ($newDSDownTimes as $dsDownTime) {
                if ($dsDownTime->getStartTimestamp() <= $firstTS && $dsDownTime->getEndTimestamp() >= $lastTS) {
                    throw new VisitSearchFailure("Failed to find a visit in the requested time range.", 500);
                }

                if ($dsDownTime->getEndTimestamp() <= $firstTS) {
                    continue;
                } elseif ($dsDownTime->getStartTimestamp() <= $firstTS) {
                    $firstTS = $dsDownTime->getEndTimestamp();
                    $this->validateTimeRange($firstTS, $lastTS, $consumingTime);
                    continue;
                }

                if (isset($previousDSDownTime)) {
                    $previousBlock = $previousDSDownTime->getEndTimestamp();
                } else {
                    $previousBlock = $firstTS;
                }

                if ($dsDownTime->getStartTimestamp() > $lastTS) {
                    $currentBlock = $lastTS;
                } else {
                    $currentBlock = $dsDownTime->getStartTimestamp();
                }

                if (
                    $previousBlock >= $firstTS &&
                    $currentBlock <= $lastTS &&
                    ($currentBlock - $previousBlock) >= $consumingTime
                ) {
                    try {
                        return $this->SearchingBetweenTimeRange->search($previousBlock, $currentBlock, $consumingTime, $futureVisits);
                    } catch (VisitSearchFailure $th) {
                    } catch (NeededTimeOutOfRange $th) {
                    }
                }

                /** @var DSDownTime $previousDSDownTime */
                $previousDSDownTime = $dsDownTime;

                if ($dsDownTime->getEndTimestamp() > $lastTS) {
                    break;
                }
            }

            if ($dsDownTime->getEndTimestamp() < $lastTS) {
                if ($dsDownTime->getEndTimestamp() >= $firstTS && ($lastTS - $dsDownTime->getEndTimestamp()) >= $consumingTime) {
                    $previousBlock = $dsDownTime->getEndTimestamp();
                } else {
                    $previousBlock = $firstTS;
                }

                try {
                    return $this->SearchingBetweenTimeRange->search($dsDownTime->getEndTimestamp(), $lastTS, $consumingTime, $futureVisits);
                } catch (VisitSearchFailure $th) {
                } catch (NeededTimeOutOfRange $th) {
                }
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
