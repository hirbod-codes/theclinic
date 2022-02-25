<?php

namespace TheClinic\Visit;

use TheClinicDataStructure\DataStructures\Time\DSDownTimes;
use TheClinicDataStructure\DataStructures\Visit\DSVisits;
use TheClinicDataStructure\DataStructures\Time\DSWorkSchedule;
use TheClinic\Exceptions\Visit\NeededTimeOutOfRange;
use TheClinic\Exceptions\Visit\VisitSearchFailure;
use TheClinic\Visit\IFindVisit;
use TheClinic\Visit\Utilities\DownTime;
use TheClinic\Visit\Utilities\SearchingBetweenDownTimes;
use TheClinic\Visit\Utilities\WorkSchedule;

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

    private int $recursiveSafetyLimit;

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
        $this->futureVisits = $futureVisits;
        $this->dsWorkSchedule = $dsWorkSchedule;
        $this->dsDownTimes = $dsDownTimes;
        $this->SearchingBetweenDownTimes = $SearchingBetweenDownTimes;
        $this->workSchedule = $workSchedule;
        $this->downTime = $downTime;
        $this->recursiveSafetyLimit=0;
    }

    public function findVisit(): int
    {
        return $this->finVisitRecursively();
    }

    private function finVisitRecursively(bool $isTimeChecked = false): int
    {
        $this->recursiveSafetyLimit++;
        if ($this->recursiveSafetyLimit > 500) {
            throw new \RuntimeException("RECURSIVE LIMIT REACHED!!!", 500);
        }

        if (!$isTimeChecked && $this->isTimeIncorrect($this->pointer, $this->dsDownTimes, $this->dsWorkSchedule)) {
            $this->adjustTime($this->pointer, $this->dsDownTimes, $this->dsWorkSchedule);
        }

        $date = $this->pointer->format("Y-m-d");
        $newDSWorkSchedule = $this->dsWorkSchedule->cloneIt();
        $newDSWorkSchedule->setStartingDay($this->pointer->format("l"));

        /** @var \TheClinicDataStructure\DataStructures\Time\DSTimePeriods $periods */
        foreach ($newDSWorkSchedule as $weekDay => $periods) {

            /** @var \TheClinicDataStructure\DataStructures\Time\DSTimePeriod $period */
            foreach ($periods as $period) {
                if (
                    ($this->pointer->getTimestamp() >= $period->getEndTimestamp($date)) ||
                    ($this->pointer->getTimestamp() < $period->getStartTimestamp($date)) ||
                    (($period->getEndTimestamp($date) - $this->pointer->getTimestamp()) < $this->consumingTime)
                ) {
                    continue;
                }

                try {
                    return $this->SearchingBetweenDownTimes->search($this->pointer->getTimestamp(), $period->getEndTimestamp($date), $this->futureVisits, $this->dsDownTimes, $this->consumingTime);
                } catch (VisitSearchFailure $th) {
                } catch (NeededTimeOutOfRange $th) {
                }

                $periods->next();
                if ($periods->valid()) {
                    $this->pointer->setTimestamp($periods->current()->getStartTimestamp($date));
                    $date = $this->pointer->format("Y-m-d");

                    if ($this->isTimeIncorrect($this->pointer, $this->dsDownTimes, $this->dsWorkSchedule)) {
                        $this->adjustTime($this->pointer, $this->dsDownTimes, $this->dsWorkSchedule);
                        return $this->{__FUNCTION__}(true);
                    }

                    continue;
                }

                $newDSWorkSchedule->next();
                if ($newDSWorkSchedule->valid()) {
                    $date = $this->pointer->modify("+1 day")->format("Y-m-d");

                    $this->pointer->setTimestamp($newDSWorkSchedule->current()[0]->getStartTimestamp($date));

                    if ($this->isTimeIncorrect($this->pointer, $this->dsDownTimes, $this->dsWorkSchedule)) {
                        $this->adjustTime($this->pointer, $this->dsDownTimes, $this->dsWorkSchedule);
                        return $this->{__FUNCTION__}(true);
                    }

                    $newDSWorkSchedule->prev();
                }
            }
        }

        $date = $this->pointer->modify("+1 day")->format("Y-m-d");
        $this->pointer->setTimestamp($newDSWorkSchedule[$this->pointer->format("l")][0]->getStartTimestamp($date));

        return $this->{__FUNCTION__}();
    }

    private function isTimeIncorrect(\DateTime &$dt, DSDownTimes $dsDownTimes, DSWorkSchedule $dsWorkSchedule): bool
    {
        return $this->downTime->isInDownTime($dt, $dsDownTimes) || (!$this->workSchedule->isInWorkSchedule($dt, $dsWorkSchedule));
    }

    private function adjustTime(\DateTime &$dt, DSDownTimes $dsDownTimes, DSWorkSchedule $dsWorkSchedule): void
    {
        do {
            $this->downTime->moveTimeToClosestUpTime($dt, $dsDownTimes);

            $this->workSchedule->movePointerToClosestWorkSchedule($dt, $dsWorkSchedule);
        } while ($this->downTime->isInDownTime($dt, $dsDownTimes) || (!$this->workSchedule->isInWorkSchedule($dt, $dsWorkSchedule)));
    }
}
