<?php

namespace TheClinic\Order\Laser;

use TheClinicDataStructures\DataStructures\Order\DSPackages;
use TheClinicDataStructures\DataStructures\Order\DSParts;

interface ILaserTimeConsumptionCalculator
{
    /**
     * Calculates the order's time consumption based on the provided parts and packages needed time and return the time with an integer.
     *
     * @param TheClinicDataStructures\DataStructures\Order\DSParts $parts
     * @param TheClinicDataStructures\DataStructures\Order\DSPackages $packages
     * @return integer
     */
    public function calculate(DSParts $parts, DSPackages $packages): int;
}
