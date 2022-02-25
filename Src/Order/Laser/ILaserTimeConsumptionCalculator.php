<?php

namespace TheClinic\Order\Laser;

use TheClinicDataStructure\DataStructures\Order\DSPackages;
use TheClinicDataStructure\DataStructures\Order\DSParts;

interface ILaserTimeConsumptionCalculator
{
    /**
     * Calculates the order's time consumption based on the provided parts and packages needed time and return the time with an integer.
     *
     * @param TheClinicDataStructure\DataStructures\Order\DSParts $parts
     * @param TheClinicDataStructure\DataStructures\Order\DSPackages $packages
     * @return integer
     */
    public function calculate(DSParts $parts, DSPackages $packages): int;
}
