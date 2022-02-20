<?php

namespace TheClinic\DataStructures\Order;

use TheClinic\DataStructures\User\DSUser;
use TheClinic\DataStructures\Order\DSParts;
use TheClinic\DataStructures\Order\DSPackages;
use TheClinic\DataStructures\Visit\DSVisits;
use TheClinic\Exceptions\DataStructures\Order\InvalidGenderException;

class DSOrder
{
    private int $id;

    private DSUser $user;

    private DSParts $parts;

    private DSPackages $packages;

    public DSVisits|null $visits;

    private int $price;

    private int $neededTime;

    private \DateTime $createdAt;

    private \DateTime $updatedAt;

    public function __construct(
        int $id,
        DSUser $user,
        DSParts $parts,
        DSPackages $packages,
        ?DSVisits $visits = null,
        int $price,
        int $neededTime,
        \DateTime $createdAt,
        \DateTime $updatedAt
    ) {
        $this->id = $id;
        $this->user = $user;
        $this->setParts($parts);
        $this->setPackages($packages);
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

    public function getParts(): DSParts
    {
        return $this->parts;
    }

    public function setParts(DSParts $parts): void
    {
        if ((isset($this->packages) && $this->packages->getGender() !== $parts->getGender()) ||
            ($parts->getGender() !== $this->user->getGender())
        ) {
            throw new InvalidGenderException("Parts gender doesn't match with this data structures' order or package gender.", 500);
        }

        $this->parts = $parts;
    }

    public function getPackages(): DSPackages
    {
        return $this->packages;
    }

    public function setPackages(DSPackages $packages): void
    {
        if ((isset($this->parts) && $packages->getGender() !== $this->parts->getGender()) ||
            ($packages->getGender() !== $this->user->getGender())
        ) {
            throw new InvalidGenderException("Packages gender doesn't match with this data structures' order or part gender.", 500);
        }

        $this->packages = $packages;
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
