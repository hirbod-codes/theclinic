<?php

namespace TheClinic\Visit\Utilities;

use TheClinic\DataStructures\Visit\DSVisits;
use TheClinic\Exceptions\Visit\NeededTimeOutOfRange;
use TheClinic\Exceptions\Visit\VisitSearchFailure;

class SearchingBetweenTimeRange
{
    public function search(int $firstTS, int $lastTS, int $consumingTime, DSVisits $futureVisits): int
    {
        if (($lastTS - $firstTS) < $consumingTime) {
            throw new NeededTimeOutOfRange("Incorrect time range(" . strval($firstTS - $lastTS) . ") for requested time consumption(" . strval($consumingTime) . ".)", 500);
        }

        if (count($futureVisits) === 0) {
            return $firstTS;
        }

        /** @var \TheClinic\DataStructures\Visit\DSVisit $visit */
        foreach ($futureVisits as $visit) {
            if ($visit->getVisitTimestamp() < $firstTS && $visit->getVisitTimestamp() < $lastTS) {
                continue;
            }

            if ($futureVisits->key() === 0 && $visit->getVisitTimestamp() > $firstTS && ($visit->getVisitTimestamp() - $firstTS) >= $consumingTime) {
                return $firstTS;
            }

            $futureVisits->prev();
            if ($futureVisits->valid()) {
                $previousVisit = $futureVisits->current();

                if (($visit->getVisitTimestamp() - ($previousVisit->getVisitTimestamp() + $previousVisit->getConsumingTime())) >= $consumingTime) {
                    return $previousVisit->getVisitTimestamp() + $previousVisit->getConsumingTime();
                }
            }
            $futureVisits->next();
        }

        if (($visit->getVisitTimestamp() + $visit->getConsumingTime() + $consumingTime) <= $lastTS) {
            return $visit->getVisitTimestamp() + $visit->getConsumingTime();
        }

        throw new VisitSearchFailure("Failed to find a visit in requested time range.", 500);
    }
}
