<?php

namespace TheClinic\Order\Laser\Calculations;

use TheClinicDataStructures\DataStructures\Order\DSPackages;
use TheClinicDataStructures\DataStructures\Order\DSParts;
use TheClinic\Order\Laser\Calculations\TraitCollectDistinguishedParts;
use TheClinic\Order\Laser\ILaserTimeConsumptionCalculator;

class TimeConsumptionCalculator implements ILaserTimeConsumptionCalculator
{
    use TraitCollectDistinguishedParts;

    /**
     * @param \TheClinicDataStructures\DataStructures\Order\DSParts $parts
     * @param \TheClinicDataStructures\DataStructures\Order\DSPackages $packages
     * @return integer
     */
    public function calculate(DSParts $parts, DSPackages $packages): int
    {
        $neededTime = 0;

        /** @var \TheClinicDataStructures\DataStructures\Order\DSPart $part */
        foreach ($this->collectDistinguishedParts($parts, $packages) as $part) {
            $neededTime += $part->getNeededTime();
        }

        return $neededTime;
    }
}
