<?php

namespace TheClinic\Visit\Utilities;

use TheClinicDataStructures\DataStructures\Visit\DSVisits;
use TheClinic\Exceptions\Visit\NeededTimeOutOfRange;
use TheClinic\Exceptions\Visit\VisitSearchFailure;
use TheClinicDataStructures\DataStructures\Visit\DSVisit;

class SearchingBetweenTimeRange
{
    public function search(int $firstTS, int $lastTS, int $consumingTime, DSVisits $futureVisits): int
    {
        // For testing purposes
        $firsDT = (new \DateTime)->setTimestamp($firstTS);
        $lastDT = (new \DateTime)->setTimestamp($lastTS);

        $this->validateTimeRange($firstTS, $lastTS, $consumingTime);

        if (count($futureVisits) === 0) {
            return $firstTS;
        }

        /** @var \TheClinicDataStructures\DataStructures\Visit\DSVisit $visit */
        foreach ($futureVisits as $visit) {
            $visitEnd = $visit->getVisitTimestamp() + $visit->getConsumingTime();

            if ($visit->getVisitTimestamp() <= $firstTS && $visitEnd >= $lastTS) {
                throw new VisitSearchFailure("Failed to find a visit in requested time range.", 500);
            }

            if ($visitEnd <= $firstTS) {
                continue;
            } elseif ($visit->getVisitTimestamp() <= $firstTS) {
                $firstTS = $visitEnd;
                $this->validateTimeRange($firstTS, $lastTS, $consumingTime);
                continue;
            }

            if (isset($previousVisit)) {
                $previousBlock = $previousVisit->getVisitTimestamp() + $previousVisit->getConsumingTime();
            } else {
                $previousBlock = $firstTS;
            }

            if ($visit->getVisitTimestamp() > $lastTS) {
                $currentBlock = $lastTS;
            } else {
                $currentBlock = $visit->getVisitTimestamp();
            }

            if (
                $previousBlock >= $firstTS &&
                $currentBlock <= $lastTS &&
                ($currentBlock - $previousBlock) >= $consumingTime
            ) {
                return $previousBlock;
            }

            /** @var DSVisit $visit */
            $previousVisit = $visit;

            if (($visitEnd + $consumingTime) > $lastTS) {
                break;
            }
        }

        if ($visitEnd < $lastTS) {
            if ($visitEnd >= $firstTS && ($lastTS - $visitEnd) >= $consumingTime) {
                return $visitEnd;
            } else {
                return $firstTS;
            }
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
