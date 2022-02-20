<?php

namespace TheClinic\DataStructures\Time;

use TheClinic\Exceptions\DataStructures\Time\TimeSequenceViolationException;

class DSDateTimePeriod
{
    protected \DateTime $start;

    protected \DateTime $end;

    public function __construct(\DateTime $start, \DateTime $end)
    {
        $this->setStart($start);
        $this->setEnd($end);
    }

    public function setStart(\DateTime $start): void
    {
        if (isset($this->end) && $start->getTimestamp() >= $this->end->getTimestamp()) {
            throw new TimeSequenceViolationException("The 'start' property must be before the 'end' property.(in terms of time)", 500);
        }

        $this->start = $start;
    }

    public function getStart(): \DateTime
    {
        return $this->start;
    }

    public function setEnd(\DateTime $end): void
    {
        if (isset($this->start) && $end->getTimestamp() <= $this->start->getTimestamp()) {
            throw new TimeSequenceViolationException("The 'start' property must be before the 'end' property.(in terms of time)", 500);
        }

        $this->end = $end;
    }

    public function getEnd(): \DateTime
    {
        return $this->end;
    }

    public function getStartTimestamp(): int
    {
        return $this->start->getTimestamp();
    }

    public function getEndTimestamp(): int
    {
        return $this->end->getTimestamp();
    }

    public function cloneIt(): DSDateTimePeriod
    {
        return new DSDateTimePeriod((new \DateTime())->setTimestamp($this->start->getTimestamp()), (new \DateTime())->setTimestamp($this->end->getTimestamp()));
    }
}
