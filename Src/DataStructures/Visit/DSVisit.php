<?php

namespace TheClinic\DataStructures\Visit;

use TheClinic\DataStructures\Time\DSDateTimePeriod;
use TheClinic\DataStructures\Order\DSOrder;
use TheClinic\DataStructures\Time\DSWeekDaysPeriods;
use TheClinic\DataStructures\User\DSUser;

class DSVisit
{
    private int $id;

    public DSUser|null $user;

    public DSOrder|null $order;

    private int $visitTimestamp;

    private int $consumingTime;

    public null|DSWeekDaysPeriods $weekDaysPeriods;

    public null|DSDateTimePeriod $dateTimePeriod;

    private \DateTime $createdAt;

    private \DateTime $updatedAt;

    public function __construct(
        int $id,
        DSUser|null $user = null,
        DSOrder|null $order = null,
        int $visitTimestamp,
        int $consumingTime,
        DSWeekDaysPeriods|null $weekDaysPeriods = null,
        DSDateTimePeriod|null $dateTimePeriod = null,
        \DateTime $createdAt,
        \DateTime $updatedAt
    ) {
        $this->id = $id;
        $this->user = $user;
        $this->order = $order;
        $this->visitTimestamp = $visitTimestamp;
        $this->consumingTime = $consumingTime;
        $this->weekDaysPeriods = $weekDaysPeriods;
        $this->dateTimePeriod = $dateTimePeriod;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    public function setId(int $val): void
    {
        $this->id = $val;
    }

    public function setVisitTimestamp(int $val): void
    {
        $this->visitTimestamp = $val;
    }

    public function setConsumingTime(int $val): void
    {
        $this->consumingTime = $val;
    }

    public function setCreatedAt(\DateTime $val): void
    {
        $this->createdAt = $val;
    }

    public function setUpdatedAt(\DateTime $val): void
    {
        $this->updatedAt = $val;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getVisitTimestamp(): int
    {
        return $this->visitTimestamp;
    }

    public function getConsumingTime(): int
    {
        return $this->consumingTime;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTime
    {
        return $this->updatedAt;
    }
}
