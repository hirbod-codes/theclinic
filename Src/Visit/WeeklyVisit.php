<?php

namespace TheClinic\Visit;

use TheClinic\Exceptions\Visit\NeededTimeOutOfRange;
use TheClinic\Exceptions\Visit\VisitSearchFailure;
use TheClinic\Visit\Utilities\DownTime;
use TheClinic\Visit\Utilities\SearchingBetweenDownTimes;
use TheClinic\Visit\Utilities\WorkSchedule;
use TheClinicDataStructures\DataStructures\Time\DSDateTimePeriod;
use TheClinicDataStructures\DataStructures\Time\DSDateTimePeriods;
use TheClinicDataStructures\DataStructures\Time\DSDownTimes;
use TheClinicDataStructures\DataStructures\Time\DSWeekDaysPeriods;
use TheClinicDataStructures\DataStructures\Time\DSWorkSchedule;
use TheClinicDataStructures\DataStructures\Visit\DSVisits;

class WeeklyVisit implements IFindVisit
{
    private DSWeekDaysPeriods $dsWeekDaysPeriods;

    private int $consumingTime;

    private DSVisits $futureVisits;

    private DSWorkSchedule $dsWorkSchedule;

    private DSDownTimes $dsDownTimes;

    private \DateTime $startingPoint;

    private SearchingBetweenDownTimes $SearchingBetweenDownTimes;

    private WorkSchedule $workSchedule;

    private DownTime $downTime;

    private string $oldSort;

    public function __construct(
        DSWeekDaysPeriods $dsWeekDaysPeriods,
        int $consumingTime,
        DSVisits $futureVisits,
        DSWorkSchedule $dsWorkSchedule,
        DSDownTimes $dsDownTimes,
        null|\DateTime $startingPoint = null,
        null|SearchingBetweenDownTimes $SearchingBetweenDownTimes = null,
        null|WorkSchedule $workSchedule = null,
        null|DownTime $downTime = null,
    ) {
        // $this->validateTimeRange($start, $end, $consumingTime);
        $this->dsWeekDaysPeriods = $dsWeekDaysPeriods;
        $this->consumingTime = $consumingTime;

        $this->oldSort = $futureVisits->getSort();
        $futureVisits->setSort('ASC');
        $this->futureVisits = $futureVisits;

        $this->dsWorkSchedule = $dsWorkSchedule;
        $this->dsDownTimes = $dsDownTimes;

        $this->startingPoint = $startingPoint ?: new \DateTime;
        $this->SearchingBetweenDownTimes = $SearchingBetweenDownTimes ?: new SearchingBetweenDownTimes;
        $this->workSchedule = $workSchedule ?: new WorkSchedule;
        $this->downTime = $downTime ?: new DownTime;
    }

    public function findVisit(): int
    {
        try {
            $timestamps = [];

            /**
             * @var DSDateTimePeriods $dsWeekDayPeriods 
             * @var string $weekDay
             */
            foreach ($this->dsWeekDaysPeriods as $weekDay => $dsWeekDayPeriods) {
                $timestamp = $this->iterateDSWeekDayPeriods($dsWeekDayPeriods, $weekDay);

                // For testing purposes
                $timestampDT = (new \DateTime)->setTimestamp($timestamp);

                if ((new \DateTime)->setTimestamp($timestamp)->format('l') !== $weekDay) {
                    throw new \LogicException('The founded visit time doesn\'t match with provided information.', 500);
                }

                $timestamps[] = $timestamp;
            }

            return $timestamp = $this->findClosestTimestamp($timestamps);
        } finally {
            $this->futureVisits->setSort($this->oldSort);
        }
    }

    /**
     * @param integer[] $timestamps
     * @return integer
     */
    private function findClosestTimestamp(array $timestamps): int
    {
        $first = true;
        foreach ($timestamps as $timestamp) {
            if ($first) {
                $first = false;
                /** @var int $smallestTimestamp */
                $smallestTimestamp = $timestamp;
                continue;
            }

            if ($timestamp < $smallestTimestamp) {
                $smallestTimestamp = $timestamp;
            }
        }

        return $smallestTimestamp;
    }

    private function iterateDSWeekDayPeriods(DSDateTimePeriods $dsWeekDayPeriods, string $weekDay): int
    {
        /** @var DSDateTimePeriod $dsWeekDayPeriod */
        foreach ($dsWeekDayPeriods as $dsWeekDayPeriod) {
            $this->validateTimeRange($dsWeekDayPeriod->getStart(), $dsWeekDayPeriod->getEnd(), $this->consumingTime);

            return $this->iterateDSWorkSchedule($dsWeekDayPeriod, $weekDay);
        }
    }

    private function iterateDSWorkSchedule(DSDateTimePeriod $dsWeekDayPeriod, string $weekDay): int
    {
        $today = (new \DateTime)->setTimestamp($this->startingPoint->getTimestamp());
        $this->moveToWeekDay($today, $weekDay);

        $safety = 0;
        while (!isset($timestamp) && $safety < 500) {
            $dsWeekDayPeriod->setDate($today);

            /**
             * @var DSDateTimePeriod $dsWorkSchedulePeriod
             * @var DSDateTimePeriods $dsWorkSchedulePeriods
             */
            foreach ($dsWorkSchedulePeriods = $this->dsWorkSchedule[$weekDay] as $dsWorkSchedulePeriod) {
                $dsWorkSchedulePeriod->setDate($today);

                try {
                    $timestamp = $this->searchInIntersection(
                        $dsWorkSchedulePeriod,
                        $dsWeekDayPeriod
                    );

                    if ($timestamp < $this->startingPoint->getTimestamp()) {
                        throw new VisitSearchFailure('', 500);
                    }

                    return $timestamp;
                } catch (VisitSearchFailure $th) {
                } catch (NeededTimeOutOfRange $th) {
                }
            }

            $today->modify('+7 days');
            $safety++;
        }

        throw new \RuntimeException('Sefety limit reached, yet no visits has found!!!', 500);
    }

    private function searchInIntersection(DSDateTimePeriod $dsWorkSchedulePeriod, DSDateTimePeriod $dsWeekDayPeriod): int
    {
        if (
            ($dsWeekDayPeriod->getStartTimestamp() < $dsWorkSchedulePeriod->getStartTimestamp() &&
                $dsWeekDayPeriod->getEndTimestamp() <= $dsWorkSchedulePeriod->getStartTimestamp())
            ||
            ($dsWeekDayPeriod->getStartTimestamp() >= $dsWorkSchedulePeriod->getEndTimestamp() &&
                $dsWeekDayPeriod->getEndTimestamp() > $dsWorkSchedulePeriod->getEndTimestamp())
        ) {
            throw new NeededTimeOutOfRange("", 1);
        }

        if ($dsWeekDayPeriod->getStartTimestamp() > $dsWorkSchedulePeriod->getStartTimestamp()) {
            $previousBlock = $dsWeekDayPeriod->getStartTimestamp();
        } else {
            $previousBlock = $dsWorkSchedulePeriod->getStartTimestamp();
        }

        if ($dsWeekDayPeriod->getEndTimestamp() < $dsWorkSchedulePeriod->getEndTimestamp()) {
            $currentBlock = $dsWeekDayPeriod->getEndTimestamp();
        } else {
            $currentBlock = $dsWorkSchedulePeriod->getEndTimestamp();
        }

        return $this->SearchingBetweenDownTimes->search(
            $previousBlock,
            $currentBlock,
            $this->futureVisits,
            $this->dsDownTimes,
            $this->consumingTime
        );
    }

    private function moveToWeekDay(\DateTime &$today, string $weekDay): void
    {
        if (!in_array($weekDay, DSWeekDaysPeriods::$weekDays)) {
            throw new \InvalidArgumentException('Wrong name for a day of a week.The given name: ' . $weekDay, 500);
        }

        while ($today->format('l') !== $weekDay) {
            $today->modify('+1 day');
        }
    }

    private function validateTimeRange(\DateTime $start, \DateTime $end, int $consumingTime): void
    {
        if (
            $end->getTimestamp() <= $start->getTimestamp() ||
            ($end->getTimestamp() - $start->getTimestamp()) < $consumingTime
        ) {
            throw new NeededTimeOutOfRange();
        }
    }
}
