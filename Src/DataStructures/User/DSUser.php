<?php

namespace TheClinic\DataStructures\User;

use TheClinic\DataStructures\Order\DSOrders;
use TheClinic\DataStructures\Visit\DSVisits;
use TheClinic\DataStructures\User\IUserRule;
use TheClinic\DataStructures\User\ICheckAuthentication;

class DSUser
{
    private ICheckAuthentication $iCheckAuthentication;

    private IUserRule $role;

    private int $id;

    private string $firstname;

    private string $lastname;

    private string $username;

    private string $password;

    private string $gender;

    public DSVisits|null $visits;

    public DSOrders|null $orders;

    private \DateTime $createdAt;

    private \DateTime $updatedAt;

    public function __construct(
        ICheckAuthentication $iCheckAuthentication,
        IUserRule $role,
        int $id,
        string $firstname,
        string $lastname,
        string $username,
        string $password,
        string $gender,
        DSVisits|null $visits = null,
        DSOrders|null $orders = null,
        \DateTime $createdAt,
        \DateTime $updatedAt,
    ) {
        $this->iCheckAuthentication = $iCheckAuthentication;
        $this->role = $role;
        $this->id = $id;
        $this->firstname = $firstname;
        $this->lastname = $lastname;
        $this->username = $username;
        $this->password = $password;
        $this->gender = $gender;
        $this->visits = $visits;
        $this->orders = $orders;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    public function isAuthenticated(): bool
    {
        return $this->iCheckAuthentication->isAuthenticated();
    }

    // rule
    public function getRule(): IUserRule
    {
        return $this->role;
    }

    public function setRule(IUserRule $role): void
    {
        $this->role = $role;
    }

    // id
    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $var): void
    {
        $this->id = $var;
    }

    // firstname
    public function getFirstname(): string
    {
        return $this->firstname;
    }

    public function setFirstname(string $var): void
    {
        $this->firstname = $var;
    }

    // lastname
    public function getLastname(): string
    {
        return $this->lastname;
    }

    public function setLastname(string $var): void
    {
        $this->lastname = $var;
    }

    // username
    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $var): void
    {
        $this->username = $var;
    }

    // password
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    // gender
    public function getGender(): string
    {
        return $this->gender;
    }

    public function setGender(string $var): void
    {
        $this->gender = $var;
    }

    // createdAt
    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $var): void
    {
        $this->createdAt = $var;
    }

    // updatedAt
    public function getUpdatedAt(): \DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTime $var): void
    {
        $this->updatedAt = $var;
    }
}
