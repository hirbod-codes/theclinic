<?php

namespace TheClinic\Order\Laser;

use TheClinic\DataStructures\Order\DSPackages;
use TheClinic\DataStructures\Order\DSParts;

interface ILaserTimeConsumptionCalculator
{
    /**
     * Calculates the order's time consumption based on the provided parts and packages needed time and return the time with an integer.
     *
     * @param TheClinic\DataStructures\Order\DSParts $parts
     * @param TheClinic\DataStructures\Order\DSPackages $packages
     * @return integer
     */
    public function calculate(DSParts $parts, DSPackages $packages): int;
}
