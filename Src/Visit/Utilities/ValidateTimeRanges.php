<?php

namespace TheClinic\Visit\Utilities;

use TheClinic\Exceptions\Visit\NeededTimeOutOfRange;
use TheClinicDataStructures\DataStructures\Time\DSDateTimePeriod;
use TheClinicDataStructures\DataStructures\Time\DSDateTimePeriods;
use TheClinicDataStructures\DataStructures\Time\DSWorkSchedule;

class ValidateTimeRanges
{
    public function checkConsumingTimeInWorkSchedule(DSWorkSchedule $dsWorkSchedule, int $consumingTime): void
    {
        $found = false;

        /**
         * @var string $weekDay
         * @var DSDateTimePeriods $dsDateTimePeriods
         */
        foreach ($dsWorkSchedule as $weekDay => $dsDateTimePeriods) {
            /** @var DSDateTimePeriod $dsDateTimePeriod */
            foreach ($dsDateTimePeriods as $dsDateTimePeriod) {
                if (($dsDateTimePeriod->getEndTimestamp() - $dsDateTimePeriod->getStartTimestamp()) >= $consumingTime) {
                    $found = true;
                }
            }
        }

        if (!$found) {
            throw new NeededTimeOutOfRange('There is not enough time for this order in the given work schedule.', 500);
        }
    }

    public function checkConsumingTimeInTimeRange(int $firstTS, int $lastTS, int $consumingTime): void
    {
        if (
            $lastTS <= $firstTS ||
            ($lastTS - $firstTS) < $consumingTime
        ) {
            throw new NeededTimeOutOfRange();
        }
    }
}
