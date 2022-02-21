<?php

namespace TheClinic\DataStructures\User;

interface ICheckAuthentication
{
    /**
     * Checks if we have an authenticated user.
     *
     * @return boolean
     */
    public function isAuthenticated(): bool;
}
