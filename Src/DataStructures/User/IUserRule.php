<?php

namespace TheClinic\DataStructures\User;

interface IUserRule
{
    public function getName(): string;
    public function privilegeExists(string $privilege): bool;
    public function getPrivileges(): array;
    public function getPrivilegeValue(string $privilege): mixed;
}
