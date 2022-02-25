<?php

namespace TheClinic\Visit\Utilities;

use TheClinicDataStructures\DataStructures\Time\DSDownTimes;
use TheClinicDataStructures\DataStructures\Visit\DSVisits;
use TheClinic\Exceptions\Visit\NeededTimeOutOfRange;
use TheClinic\Exceptions\Visit\VisitSearchFailure;

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
        $firstDT = (new \DateTime)->setTimestamp($firstTS);
        $lastDT = (new \DateTime)->setTimestamp($lastTS);
        $intruptingDSDownTimes = $this->dowTime->findDownTimeIntruptionWithTimeRange($firstTS, $lastTS, $dsDownTimes);

        if (count($intruptingDSDownTimes) === 0) {
            return $this->SearchingBetweenTimeRange->search($firstTS, $lastTS, $consumingTime, $futureVisits);
        } else {
            $newDSDownTimes = $intruptingDSDownTimes->cloneIt();

            /** @var \TheClinicDataStructures\DataStructures\Time\DSDownTime $dsDownTime */
            foreach ($newDSDownTimes as $dsDownTime) {
                if ($dsDownTime->getStartTimestamp() <= $firstTS) {
                    continue;
                }

                if ($newDSDownTimes->key() === 0 && $dsDownTime->getStartTimestamp() > $firstTS && ($dsDownTime->getStartTimestamp() - $firstTS) >= $consumingTime) {
                    return $firstTS;
                }

                $newDSDownTimes->prev();
                if ($newDSDownTimes->valid() && $newDSDownTimes->current()->getEndTimestamp() > $firstTS) {
                    $firstTS = $newDSDownTimes->current()->getEndTimestamp();
                }
                $newDSDownTimes->next();

                try {
                    return $this->SearchingBetweenTimeRange->search($firstTS, $dsDownTime->getStartTimestamp(), $consumingTime, $futureVisits);
                } catch (VisitSearchFailure $th) {
                } catch (NeededTimeOutOfRange $th) {
                }
            }

            if ($dsDownTime->getEndTimestamp() < $lastTS) {
                try {
                    return $this->SearchingBetweenTimeRange->search($dsDownTime->getEndTimestamp(), $lastTS, $consumingTime, $futureVisits);
                } catch (VisitSearchFailure $th) {
                } catch (NeededTimeOutOfRange $th) {
                }
            }
        }

        throw new VisitSearchFailure("Failed to find a visit in the requested time range.", 500);
    }
}
