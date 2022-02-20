<?php

namespace TheClinic\DataStructures\Time;

use TheClinic\DataStructures\Time\DSDateTimePeriod;

class DSDownTime extends DSDateTimePeriod
{
    public string $name;

    public function cloneIt(): DSDownTime
    {
        return new DSDownTime((new \DateTime())->setTimestamp($this->start->getTimestamp()), (new \DateTime())->setTimestamp($this->end->getTimestamp()));
    }
}
