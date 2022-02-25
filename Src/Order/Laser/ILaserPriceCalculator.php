<?php

namespace TheClinic\Order\Laser;

use TheClinicDataStructures\DataStructures\Order\DSPackages;
use TheClinicDataStructures\DataStructures\Order\DSParts;

interface ILaserPriceCalculator
{
    /**
     * Calculates the order's price based on the provided parts and packages costs and return the price with an integer.
     *
     * @param \TheClinicDataStructures\DataStructures\Order\DSParts $parts
     * @param \TheClinicDataStructures\DataStructures\Order\DSPackages $packages
     * @return integer
     */
    public function calculate(DSParts $parts, DSPackages $packages): int;

    /**
     * Calculates the order's totall price without the discount of packages based on the provided parts and packages' parts costs and return the price with an integer.
     *
     * @param \TheClinicDataStructures\DataStructures\Order\DSParts $parts
     * @param \TheClinicDataStructures\DataStructures\Order\DSPackages $packages
     * @return integer
     */
    public function calculateWithoutDiscount(DSParts $parts, DSPackages $packages): int;
}
