<?php

namespace TheClinic\DataStructures\Time;

use TheClinic\Exceptions\DataStructures\Time\TimeSequenceViolationException;

class DSTimePeriod
{
    /**
     * @var string like "13:14:25"
     */
    private string $start;

    /**
     * @var string like "13:14:25"
     */
    private string $end;

    public function __construct(string $start, string $end)
    {
        $this->setStart($start);
        $this->setEnd($end);
    }

    public function setStart(string $start): void
    {
        if (isset($this->end) && (new \DateTime($start))->getTimestamp() >= (new \DateTime($this->end))->getTimestamp()) {
            throw new TimeSequenceViolationException("The 'start' property must be before the 'end' property.(in terms of time)", 500);
        }

        $this->start = $start;
    }

    public function getStart(): string
    {
        return $this->start;
    }

    public function setEnd(string $end): void
    {
        if (isset($this->start) && (new \DateTime($end))->getTimestamp() <= (new \DateTime($this->start))->getTimestamp()) {
            throw new TimeSequenceViolationException("The 'end' property must be after the 'start' property.(in terms of time)", 500);
        }

        $this->end = $end;
    }

    public function getEnd(): string
    {
        return $this->end;
    }


    /**
     * @param string|null $date in this format: Y-m-d
     * @return integer
     */
    public function getStartTimestamp(?string $date = null): int
    {
        return (new \DateTime(($date === null ? "" : $date . " ") . $this->start, new \DateTimeZone("UTC")))->getTimestamp();
    }

    /**
     * @param string|null $date in this format: Y-m-d
     * @return integer
     */
    public function getEndTimestamp(?string $date = null): int
    {
        return (new \DateTime(($date === null ? "" : $date . " ") . $this->end, new \DateTimeZone("UTC")))->getTimestamp();
    }

    public function cloneIt(): DSTimePeriod
    {
        return new DSTimePeriod($this->start, $this->end);
    }
}
