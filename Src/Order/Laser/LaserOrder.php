<?php

namespace TheClinic\Order\Laser;

use TheClinic\Order\ICalculateLaserOrder;
use TheClinic\Order\Laser\ILaserPriceCalculator;
use TheClinic\Order\Laser\ILaserTimeConsumptionCalculator;

use TheClinic\DataStructures\Order\DSPackages;
use TheClinic\DataStructures\Order\DSParts;
use TheClinic\Exceptions\Order\InvalidGenderException;
use TheClinic\Exceptions\Order\NoPackageOrPartException;

class LaserOrder implements ICalculateLaserOrder
{
    private DSParts|null $parts;

    private DSPackages|null $packages;

    private ILaserTimeConsumptionCalculator $timeConsumptionCalculator;

    private ILaserPriceCalculator $priceCalculator;

    public function __construct(DSParts|null $parts = null, DSPackages|null $packages = null, ILaserPriceCalculator $priceCalculator, ILaserTimeConsumptionCalculator $timeConsumptionCalculator)
    {
        if (is_null($parts) && is_null($packages)) {
            throw new NoPackageOrPartException("The number of parts and packages can not be zero at same time.", 500);
        }

        if (!is_null($parts) && !is_null($packages) && ($parts->getGender() !== $packages->getGender())) {
            throw new InvalidGenderException("Packages and parts must have same gender", 500);
        }

        $this->parts = $parts;
        $this->packages = $packages;
        $this->priceCalculator = $priceCalculator;
        $this->timeConsumptionCalculator = $timeConsumptionCalculator;
    }

    public function calculatePrice(): int
    {
        return $this->priceCalculator->calculate($this->parts, $this->packages);
    }

    public function calculatePriceWithoutDiscount(): int
    {
        return $this->priceCalculator->calculateWithoutDiscount($this->parts, $this->packages);
    }

    public function calculateTimeConsumption(): int
    {
        return $this->timeConsumptionCalculator->calculate($this->parts, $this->packages);
    }
}
