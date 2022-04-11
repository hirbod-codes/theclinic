<?php

namespace Tests\Fakers\Time;

use TheClinicDataStructures\DataStructures\Time\DSDateTimePeriod;
use TheClinicDataStructures\DataStructures\Time\DSDateTimePeriods;
use TheClinicDataStructures\DataStructures\Time\DSWeekDaysPeriods;

class DSWeekDaysPeriodsFaker
{
    public function customFakeIt(array $data, string $startingDay = 'Monday'): DSWeekDaysPeriods
    {
        $dsWeekDaysPeriods = new DSWeekDaysPeriods($startingDay);

        $today = new \DateTime;
        foreach ($data as $weekDay => $periods) {
            $this->moveToWeekDay($today, $weekDay);

            $dsDateTimePeriods = new DSDateTimePeriods;
            foreach ($periods as $period) {
                $dsDateTimePeriods[] = new DSDateTimePeriod(
                    (new \DateTime($today->format('Y-m-d') . ' ' . $period[0])),
                    (new \DateTime($today->format('Y-m-d') . ' ' . $period[1])),
                );
            }

            $dsWeekDaysPeriods[$weekDay] = $dsDateTimePeriods;
        }

        return $dsWeekDaysPeriods;
    }

    public function fakeIt(string $startingDay = 'Monday'): DSWeekDaysPeriods
    {
        $dsWeekDaysPeriods = new DSWeekDaysPeriods($startingDay);

        $today = new \DateTime;
        foreach (DSWeekDaysPeriods::$weekDays as $weekDay) {
            if (in_array($weekDay, ['Monday', 'Wednesday', 'Friday'])) {
                continue;
            }

            $this->moveToWeekDay($today, $weekDay);

            $dsDateTimePeriods = new DSDateTimePeriods;
            $dsDateTimePeriods[] = new DSDateTimePeriod(
                (new \DateTime($today->format('Y-m-d')))->setTime(9, 0),
                (new \DateTime($today->format('Y-m-d')))->setTime(14, 0),
            );

            $dsDateTimePeriods[] = new DSDateTimePeriod(
                (new \DateTime($today->format('Y-m-d')))->setTime(18, 0),
                (new \DateTime($today->format('Y-m-d')))->setTime(23, 0),
            );

            $dsWeekDaysPeriods[$weekDay] = $dsDateTimePeriods;
        }

        return $dsWeekDaysPeriods;
    }

    private function moveToWeekDay(\DateTime &$dt, string $weekDay): void
    {
        while ($dt->format('l') !== $weekDay) {
            $dt->modify('+1 day');
        }
    }
}
