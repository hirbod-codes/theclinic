<?php

namespace TheClinic\DataStructures\Order;

use TheClinic\DataStructures\User\DSUser;
use TheClinic\DataStructures\Visit\DSVisits;

class DSOrder
{
    protected int $id;

    protected DSUser $user;

    protected int $price;

    protected int $neededTime;

    public DSVisits|null $visits;

    protected \DateTime $createdAt;

    protected \DateTime $updatedAt;

    public function __construct(
        int $id,
        DSUser $user,
        ?DSVisits $visits = null,
        int $price,
        int $neededTime,
        \DateTime $createdAt,
        \DateTime $updatedAt
    ) {
        $this->id = $id;
        $this->user = $user;
        $this->visits = $visits;
        $this->price = $price;
        $this->neededTime = $neededTime;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getUser(): DSUser
    {
        return $this->user;
    }

    public function setUser(DSUser $user): void
    {
        $this->user = $user;
    }

    public function getPrice(): int
    {
        return $this->price;
    }

    public function setPrice(int $price): void
    {
        $this->price = $price;
    }

    public function getNeededTime(): int
    {
        return $this->neededTime;
    }

    public function setNeededTime(int $neededTime): void
    {
        $this->neededTime = $neededTime;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getUpdatedAt(): \DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTime $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }
}
