<?php

namespace Tests\Fakers\Time;

use TheClinicDataStructures\DataStructures\Time\DSDateTimePeriod;
use TheClinicDataStructures\DataStructures\Time\DSDateTimePeriods;
use TheClinicDataStructures\DataStructures\Time\DSWorkSchedule;

class DSWorkScheduleFaker
{
    public array|null $customData;

    public bool $isCustomDataSet = false;

    /**
     * Default is 8-14 and 16-23, every day including weekends.
     * 
     * @param array|null $customData ["Monday"=>[["08:00:00", "14:00:00"], ...], "Tuesday"=>[, ...], ...]
     */
    public function __construct(array|null $customData = null)
    {
        $this->customData = $customData;
        if ($customData !== null) {
            $this->isCustomDataSet = true;
        }
    }

    public function fakeIt(): DSWorkSchedule
    {
        if ($this->isCustomDataSet) {
            return $this->fakeWithCustomData();
        } else {
            return $this->fakeWithDefaultData();
        }
    }

    private function fakeWithCustomData(): DSWorkSchedule
    {
        $dsWorkSchedule = new DSWorkSchedule("Monday");

        $counter = 0;
        foreach ($this->customData as $weekDay => $periods) {
            $dsDateTimePeriods = new DSDateTimePeriods;

            foreach ($periods as $period) {
                $dsDateTimePeriod = new DSDateTimePeriod(
                    ($dt = (new \DateTime($period[0]))->modify('+' . $counter . ' days')),
                    (new \DateTime($period[1]))->modify('+' . $counter . ' days')
                );

                $dsDateTimePeriods[] = $dsDateTimePeriod;
            }

            $dsWorkSchedule[$dt->format("l")] = $dsDateTimePeriods;
            $counter++;
        }

        return $dsWorkSchedule;
    }

    private function fakeWithDefaultData(): DSWorkSchedule
    {
        $dsWorkSchedule = new DSWorkSchedule((new \DateTime)->format('l'));

        for ($i = 0; $i < 7; $i++) {
            $dsDateTimePeriods = new DSDateTimePeriods;

            $dsDateTimePeriod = new DSDateTimePeriod(
                ($dt = (new \DateTime("00:00:00"))->modify('+' . $i . ' days')),
                (new \DateTime("6:00:00"))->modify('+' . $i . ' days')
            );
            $dsDateTimePeriods[] = $dsDateTimePeriod;

            $dsDateTimePeriod = new DSDateTimePeriod(
                ($dt = (new \DateTime("08:00:00"))->modify('+' . $i . ' days')),
                (new \DateTime("14:00:00"))->modify('+' . $i . ' days')
            );
            $dsDateTimePeriods[] = $dsDateTimePeriod;

            $dsDateTimePeriod = new DSDateTimePeriod(
                (new \DateTime("16:00:00"))->modify('+' . $i . ' days'),
                (new \DateTime("23:00:00"))->modify('+' . $i . ' days')
            );

            $dsDateTimePeriods[] = $dsDateTimePeriod;

            $dsWorkSchedule[$dt->format("l")] = $dsDateTimePeriods;
        }

        return $dsWorkSchedule;
    }
}
