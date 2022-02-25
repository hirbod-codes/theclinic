<?php

namespace TheClinic\Order\Laser;

use TheClinic\Order\ICalculateLaserOrder;
use TheClinic\Order\Laser\ILaserPriceCalculator;
use TheClinic\Order\Laser\ILaserTimeConsumptionCalculator;

use TheClinicDataStructure\DataStructures\Order\DSPackages;
use TheClinicDataStructure\DataStructures\Order\DSParts;
use TheClinic\Exceptions\Order\InvalidGenderException;
use TheClinic\Exceptions\Order\NoPackageOrPartException;

class LaserOrder implements ICalculateLaserOrder
{
    public function calculatePrice(DSParts|null $parts = null, DSPackages|null $packages = null, ILaserPriceCalculator $priceCalculator): int
    {
        $this->validateDSPackagesAndDSParts($parts, $packages);

        return $priceCalculator->calculate($parts, $packages);
    }

    public function calculatePriceWithoutDiscount(DSParts|null $parts = null, DSPackages|null $packages = null, ILaserPriceCalculator $priceCalculator): int
    {
        $this->validateDSPackagesAndDSParts($parts, $packages);

        return $priceCalculator->calculateWithoutDiscount($parts, $packages);
    }

    public function calculateTimeConsumption(DSParts|null $parts = null, DSPackages|null $packages = null, ILaserTimeConsumptionCalculator $timeConsumptionCalculator): int
    {
        $this->validateDSPackagesAndDSParts($parts, $packages);

        return $timeConsumptionCalculator->calculate($parts, $packages);
    }

    /**
     * Validates if packages and parts have the same gender, and if $parts and $packages are not both null.
     * 
     * @param \TheClinicDataStructure\DataStructures\Order\DSParts|null|null $parts
     * @param \TheClinicDataStructure\DataStructures\Order\DSPackages|null|null $packages
     * @return void
     * 
     * @throws NoPackageOrPartException
     * @throws InvalidGenderException
     */
    private function validateDSPackagesAndDSParts(DSParts|null $parts = null, DSPackages|null $packages = null): void
    {
        if (is_null($parts) && is_null($packages)) {
            throw new NoPackageOrPartException("The number of parts and packages can not be zero at same time.", 500);
        }

        if (!is_null($parts) && !is_null($packages) && ($parts->getGender() !== $packages->getGender())) {
            throw new InvalidGenderException("Packages and parts must have same gender", 500);
        }
    }
}
