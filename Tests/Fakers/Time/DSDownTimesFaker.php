<?php

namespace Tests\Fakers\Time;

use TheClinicDataStructures\DataStructures\Time\DSDownTime;
use TheClinicDataStructures\DataStructures\Time\DSDownTimes;

class DSDownTimesFaker
{
    public array|null $customData;

    public bool $isCustomDataSet = false;

    /**
     * Default is two downTimes("09:00:00"-"12:00:00", "17:00:00"-"20:00:00") in 5 days, starting from today.
     * 
     * @param array|null $customData [[\DateTime $start, \DateTime $end, string $name], ...]
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
            $dsDownTime = new DSDownTime($period[0], $period[1], $period[2]);

            $dsDownTimes[] = $dsDownTime;
        }

        return $dsDownTimes;
    }

    /**
     * Default is two downTimes("09:00:00"-"12:00:00", "17:00:00"-"20:00:00") in 5 days, starting from today.
     *
     * @return \TheClinicDataStructures\DataStructures\Time\DSDownTimes
     */
    private function fakeWithDefaultData(): DSDownTimes
    {
        $dsDownTimes = new DSDownTimes;

        $pointer = new \DateTime("09:00:00");

        for ($i = 0; $i < 5; $i++) {
            $fakeName = 'fakeName_' . strval(microtime(true));
            $start = (new \DateTime)->setTimestamp($pointer->getTimestamp());
            $end = (new \DateTime)->setTimestamp($pointer->modify('+3 hours')->getTimestamp());
            $dsDownTime = new DSDownTime($start, $end, $fakeName . '_' . $i);
            $dsDownTimes[] = $dsDownTime;

            $fakeName = 'fakeName_' . strval(microtime(true));
            $start = (new \DateTime)->setTimestamp($pointer->modify('+5 hours')->getTimestamp());
            $end = (new \DateTime)->setTimestamp($pointer->modify('+3 hours')->getTimestamp());
            $dsDownTime = new DSDownTime($start, $end, $fakeName . '_' . rand(100, 1000));
            $dsDownTimes[] = $dsDownTime;

            $pointer
                ->setTime(9, 0)
                ->modify('+1 day')
                // 
            ;
        }

        return $dsDownTimes;
    }
}
