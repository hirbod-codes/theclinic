<?php

namespace Tests\Fakers\Time;

use TheClinicDataStructure\DataStructures\Time\DSTimePeriod;
use TheClinicDataStructure\DataStructures\Time\DSTimePeriods;
use TheClinicDataStructure\DataStructures\Time\DSWorkSchedule;

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

        foreach ($this->customData as $weekDay => $periods) {
            $dsTimePeriods = new DSTimePeriods;

            foreach ($periods as $period) {
                $dsTimePeriod = new DSTimePeriod($period[0], $period[1]);

                $dsTimePeriods[] = $dsTimePeriod;
            }

            $dsWorkSchedule[$weekDay] = $dsTimePeriods;
        }

        return $dsWorkSchedule;
    }

    private function fakeWithDefaultData(): DSWorkSchedule
    {
        $dsWorkSchedule = new DSWorkSchedule("Monday");

        for ($i = 0; $i < 7; $i++) {
            $dsTimePeriods = new DSTimePeriods;

            $dsTimePeriod = new DSTimePeriod("08:00:00", "14:00:00");
            $dsTimePeriods[] = $dsTimePeriod;

            $dsTimePeriod = new DSTimePeriod("16:00:00", "23:00:00");
            $dsTimePeriods[] = $dsTimePeriod;

            $dsWorkSchedule[DSWorkSchedule::$weekDays[$i]] = $dsTimePeriods;
        }

        return $dsWorkSchedule;
    }
}
