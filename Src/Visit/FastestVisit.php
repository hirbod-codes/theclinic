<?php

namespace TheClinic\Visit;

use TheClinicDataStructures\DataStructures\Time\DSDownTimes;
use TheClinicDataStructures\DataStructures\Visit\DSVisits;
use TheClinicDataStructures\DataStructures\Time\DSWorkSchedule;
use TheClinic\Exceptions\Visit\NeededTimeOutOfRange;
use TheClinic\Exceptions\Visit\VisitSearchFailure;
use TheClinic\Visit\IFindVisit;
use TheClinic\Visit\Utilities\DownTime;
use TheClinic\Visit\Utilities\SearchingBetweenDownTimes;
use TheClinic\Visit\Utilities\WorkSchedule;
use TheClinicDataStructures\DataStructures\Time\DSDateTimePeriod;
use TheClinicDataStructures\DataStructures\Time\DSDateTimePeriods;

class FastestVisit implements IFindVisit
{
    private \DateTime $pointer;

    private int $consumingTime;

    private DSVisits $futureVisits;

    private DSWorkSchedule $dsWorkSchedule;

    private DSDownTimes $dsDownTimes;

    private SearchingBetweenDownTimes $SearchingBetweenDownTimes;

    private WorkSchedule $workSchedule;

    private DownTime $downTime;

    private string $oldSort;

    /**
     * Constructs a new instance.
     *
     * @param integer $initialTimeSkip
     * @param integer $consumingTime
     * @param DSVisits $futureVisits
     * @param DSWorkSchedule $dsWorkSchedule
     * @param DSDownTimes $dsDownTimes
     * @param SearchingBetweenDownTimes $SearchingBetweenDownTimes
     */
    public function __construct(
        \DateTime $startPoint,
        int $consumingTime,
        DSVisits $futureVisits,
        DSWorkSchedule $dsWorkSchedule,
        DSDownTimes $dsDownTimes,
        SearchingBetweenDownTimes $SearchingBetweenDownTimes,
        WorkSchedule $workSchedule,
        DownTime $downTime
    ) {
        $this->pointer = $startPoint;
        $this->consumingTime = $consumingTime;
        $this->oldSort = $futureVisits->getSort();
        $futureVisits->setSort('ASC');
        $this->futureVisits = $futureVisits;
        $this->dsWorkSchedule = $dsWorkSchedule;
        $this->dsDownTimes = $dsDownTimes;
        $this->SearchingBetweenDownTimes = $SearchingBetweenDownTimes;
        $this->workSchedule = $workSchedule;
        $this->downTime = $downTime;
        $this->recursiveSafetyLimit = 0;
    }

    public function findVisit(): int
    {
        $recursiveSafetyLimit = 0;

        while (!isset($timestamp) && $recursiveSafetyLimit < 500) {
            $newDSWorkSchedule = $this->dsWorkSchedule->cloneIt();
            $newDSWorkSchedule->setStartingDay($this->pointer->format("l"));

            /** @var DSDateTimePeriods $periods */
            foreach ($newDSWorkSchedule as $weekDay => $periods) {
                /** @var DSDateTimePeriod $period */
                foreach ($periods as $period) {
                    $periodStartTS = (new \DateTime($this->pointer->format('Y-m-d') . ' ' . $period->getStart()->format('H:i:s')))->getTimestamp();
                    $periodEndTS = (new \DateTime($this->pointer->format('Y-m-d') . ' ' . $period->getEnd()->format('H:i:s')))->getTimestamp();

                    if ($this->pointer->getTimestamp() > $periodEndTS) {
                        continue;
                    }

                    if (($periodEndTS - $this->pointer->getTimestamp()) < $this->consumingTime) {
                        continue;
                    }

                    if ($this->pointer->getTimestamp() <= $periodStartTS) {
                        $firstTS = $periodStartTS;
                    } else {
                        $firstTS = $this->pointer->getTimestamp();
                    }

                    try {
                        $timestamp = $this->SearchingBetweenDownTimes->search(
                            $firstTS,
                            $periodEndTS,
                            $this->futureVisits,
                            $this->dsDownTimes,
                            $this->consumingTime
                        );
                        // For testing purposes
                        $t = (new \DateTime)->setTimestamp($timestamp);
                        break 2;
                    } catch (VisitSearchFailure $th) {
                    } catch (NeededTimeOutOfRange $th) {
                    }
                }
                $this->pointer
                    ->setTime(0, 0)
                    ->modify('+1 day');
            }

            $recursiveSafetyLimit++;
        }

        $this->futureVisits->setSort($this->oldSort);

        if (isset($timestamp)) {
            return $timestamp;
        } else {
            throw new \LogicException('Failed to find a visit time.', 500);
        }
    }

    private function isTimeIncorrect(\DateTime $dt, DSDownTimes $dsDownTimes, DSWorkSchedule $dsWorkSchedule): bool
    {
        return $this->downTime->isInDownTime($dt, $dsDownTimes) || (!$this->workSchedule->isInWorkSchedule($dt, $dsWorkSchedule));
    }

    private function adjustTime(\DateTime &$dt, DSDownTimes $dsDownTimes, DSWorkSchedule $dsWorkSchedule): void
    {
        do {
            $this->downTime->moveTimeToClosestUpTime($dt, $dsDownTimes);
            $this->workSchedule->movePointerToClosestWorkSchedule($dt, $dsWorkSchedule);
        } while ($this->isTimeIncorrect($dt, $dsDownTimes, $dsWorkSchedule));
    }
}
