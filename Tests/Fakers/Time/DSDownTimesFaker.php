<?php

namespace Tests\Fakers\Time;

use TheClinic\DataStructures\Time\DSDownTime;
use TheClinic\DataStructures\Time\DSDownTimes;

class DSDownTimesFaker
{
    public array|null $customData;

    public bool $isCustomDataSet = false;

    /**
     * Default is two downTimes("09:00:00"-"12:00:00", "17:00:00"-"20:00:00") in 5 days, starting from today.
     * 
     * @param array|null $customData [[\DateTime $start, \DateTime $end], ...]
     */
    public function __construct(array|null $customData = null)
    {
        $this->customData = $customData;

        if ($customData !== null) {
            $this->isCustomDataSet = true;
        }
    }

    public function fakeIt(): DSDownTimes
    {
        if ($this->isCustomDataSet) {
            return $this->fakeWithCustomData();
        } else {
            return $this->fakeWithDefaultData();
        }
    }

    private function fakeWithCustomData(): DSDownTimes
    {
        $dsDownTimes = new DSDownTimes;

        foreach ($this->customData as $period) {
            $dsDownTime = new DSDownTime($period[0], $period[1]);

            $dsDownTimes[] = $dsDownTime;
        }

        return $dsDownTimes;
    }

    /**
     * Default is two downTimes("09:00:00"-"12:00:00", "17:00:00"-"20:00:00") in 5 days, starting from today.
     *
     * @return DSDownTimes
     */
    private function fakeWithDefaultData(): DSDownTimes
    {
        $dsDownTimes = new DSDownTimes;

        $date = (new \DateTime())->format("Y-m") . "-";
        $today = intval((new \DateTime())->format("d"));

        for ($i = 0; $i < 5; $i++) {
            $start = new \DateTime($date . strval($today + $i) . "09:00:00");
            $end = new \DateTime($date . strval($today + $i) . "12:00:00");
            $dsDownTime = new DSDownTime($start, $end);
            $dsDownTimes[] = $dsDownTime;

            $start = new \DateTime($date . strval($today + $i) . "17:00:00");
            $end = new \DateTime($date . strval($today + $i) . "20:00:00");
            $dsDownTime = new DSDownTime($start, $end);
            $dsDownTimes[] = $dsDownTime;
        }

        return $dsDownTimes;
    }
}
