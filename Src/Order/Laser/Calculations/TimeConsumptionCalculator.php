<?php

namespace TheClinic\Order\Laser\Calculations;

use TheClinicDataStructure\DataStructures\Order\DSPackages;
use TheClinicDataStructure\DataStructures\Order\DSParts;
use TheClinic\Order\Laser\Calculations\TraitCollectDistinguishedParts;
use TheClinic\Order\Laser\ILaserTimeConsumptionCalculator;

class TimeConsumptionCalculator implements ILaserTimeConsumptionCalculator
{
    use TraitCollectDistinguishedParts;

    /**
     * @param \TheClinicDataStructure\DataStructures\Order\DSParts $parts
     * @param \TheClinicDataStructure\DataStructures\Order\DSPackages $packages
     * @return integer
     */
    public function calculate(DSParts $parts, DSPackages $packages): int
    {
        $neededTime = 0;

        /** @var \TheClinicDataStructure\DataStructures\Order\DSPart $part */
        foreach ($this->collectDistinguishedParts($parts, $packages) as $part) {
            $neededTime += $part->getNeededTime();
        }

        return $neededTime;
    }
}
