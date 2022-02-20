<?php

namespace TheClinic\DataStructures\Time;

use TheClinic\DataStructures\Time\DSWeekDaysPeriods;

class DSWorkSchedule extends DSWeekDaysPeriods
{
    public function offsetUnset(mixed $offset): void
    {
        throw new \LogicException("Removing data from this data structure is not allowed.", 500);
    }
}
