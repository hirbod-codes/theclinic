<?php

namespace TheClinic\Order\Laser\Calculations;

use TheClinicDataStructure\DataStructures\Order\DSPackages;
use TheClinicDataStructure\DataStructures\Order\DSParts;
use TheClinic\Order\Laser\Calculations\TraitCollectDistinguishedParts;
use TheClinic\Order\Laser\ILaserPriceCalculator;

class PriceCalculator implements ILaserPriceCalculator
{
    use TraitCollectDistinguishedParts;

    /**
     * It sums up the prices of $parts and $packages(with their discount, each package has a price lower than sum of all of it's contained parts).
     *
     * @param \TheClinicDataStructure\DataStructures\Order\DSParts $parts
     * @param \TheClinicDataStructure\DataStructures\Order\DSPackages $packages
     * @return integer
     */
    public function calculate(DSParts $parts, DSPackages $packages): int
    {
        $price = 0;

        /** @var \TheClinicDataStructure\DataStructures\Order\DSPackage $package */
        foreach ($packages as $package) {
            $price += $package->getPrice();
        }

        /** @var \TheClinicDataStructure\DataStructures\Order\DSPart $part */
        foreach ($this->collectPartsThatDontExistInPackages($parts, $packages) as $part) {
            $price += $part->getPrice();
        }

        return $price;
    }

    /**
     * It sums up the costs of parts and parts in $packages as if there were no discount for any package.
     *
     * @param \TheClinicDataStructure\DataStructures\Order\DSParts $parts
     * @param \TheClinicDataStructure\DataStructures\Order\DSPackages $packages
     * @return integer
     */
    public function calculateWithoutDiscount(DSParts $parts, DSPackages $packages): int
    {
        $price = 0;

        /** @var \TheClinicDataStructure\DataStructures\Order\DSPart $part */
        foreach ($this->collectDistinguishedParts($parts, $packages) as $part) {
            $price += $part->getPrice();
        }

        return $price;
    }

    private function collectPartsThatDontExistInPackages(DSParts $parts, DSPackages $packages): array
    {
        $distinguishedParts = [];

        /** @var \TheClinicDataStructure\DataStructures\Order\DSPart $part */
        foreach ($parts as $part) {
            $found = true;
            
            /** @var \TheClinicDataStructure\DataStructures\Order\DSPackage $package */
            foreach ($packages as $package) {
                /** @var \TheClinicDataStructure\DataStructures\Order\DSPart $packagePart */
                foreach ($package->getParts() as $packagePart) {
                    if ($part->getId() === $packagePart->getId()) {
                        continue;
                    }

                    $found = false;
                }
            }

            if (!$found) {
                $distinguishedParts[] = $part;
            }
        }

        return $distinguishedParts;
    }
}
