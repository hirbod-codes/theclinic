<?php

namespace TheClinic\Order\Laser\Calculations;

use TheClinic\DataStructures\Order\DSPackages;
use TheClinic\DataStructures\Order\DSParts;
use TheClinic\Order\Laser\Calculations\TraitCollectDistinguishedParts;
use TheClinic\Order\Laser\ILaserTimeConsumptionCalculator;

class TimeConsumptionCalculator implements ILaserTimeConsumptionCalculator
{
    use TraitCollectDistinguishedParts;

    /**
     * @param \TheClinic\DataStructures\Order\DSParts $parts
     * @param \TheClinic\DataStructures\Order\DSPackages $packages
     * @return integer
     */
    public function calculate(DSParts $parts, DSPackages $packages): int
    {
        $neededTime = 0;

        /** @var \TheClinic\DataStructures\Order\DSPart $part */
        foreach ($this->collectDistinguishedParts($parts, $packages) as $part) {
            $neededTime += $part->getNeededTime();
        }

        return $neededTime;
    }
}
