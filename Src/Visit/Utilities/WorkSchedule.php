<?php

namespace TheClinic\Visit\Utilities;

use TheClinicDataStructure\DataStructures\Time\DSWorkSchedule;

class WorkSchedule
{
    /**
     * Moves $pointer to the nearest work schedule, if it's not in work schedule hours. otherwise returns it as it is.
     *
     * @param \DateTime $pointer
     * @param \TheClinicDataStructure\DataStructures\Time\DSWorkSchedule $dsWorkSchedule
     * @return void
     */
    public function movePointerToClosestWorkSchedule(\DateTime &$pointer, DSWorkSchedule $dsWorkSchedule): void
    {
        if ($this->isInWorkSchedule($pointer, $dsWorkSchedule)) {
            return;
        }

        $date = $pointer->format("Y-m-d");
        $pointerTS = $pointer->getTimestamp();
        $newDSWorkSchedule = $dsWorkSchedule->cloneIt();
        $newDSWorkSchedule->setStartingDay($pointer->format("l"));

        /** @var \TheClinicDataStructure\DataStructures\Time\DSTimePeriods $periods */
        foreach ($newDSWorkSchedule as $weekDay => $periods) {
            /** @var \TheClinicDataStructure\DataStructures\Time\DSTimePeriod $period */
            foreach ($periods as $period) {
                if ($pointerTS < $period->getStartTimestamp($date)) {
                    $pointer->setTimestamp($period->getStartTimestamp($date));

                    return;
                }
            }

            if ($pointerTS >= $period->getEndTimestamp($date)) {
                $newDSWorkSchedule->next();
                if ($newDSWorkSchedule->valid()) {
                    $pointer->setTimestamp($newDSWorkSchedule->current()[0]->getStartTimestamp($pointer->modify("+1 day")->format("Y-m-d")));
                    return;
                }
            }

            throw new \LogicException("Failed to find the right work schedule.", 500);
        }
    }

    /**
     * @param \DateTime $dt
     * @param \TheClinicDataStructure\DataStructures\Time\DSWorkSchedule $dsWorkSchedule
     * @return boolean
     */
    public function isInWorkSchedule(\DateTime $dt, DSWorkSchedule $dsWorkSchedule): bool
    {
        $date = $dt->format("Y-m-d");
        $dtTS = $dt->getTimestamp();
        $newDSWorkSchedule = $dsWorkSchedule->cloneIt();
        $newDSWorkSchedule->setStartingDay($dt->format("l"));

        /** @var array $workDay */
        foreach ($newDSWorkSchedule as $workDay) {
            /** @var \TheClinicDataStructure\DataStructures\Time\DSTimePeriod $period */
            foreach ($workDay as $period) {
                if ($dtTS < $period->getEndTimestamp($date) && $dtTS >= $period->getStartTimestamp($date)) {
                    return true;
                }
            }

            break;
        }

        return false;
    }
}
