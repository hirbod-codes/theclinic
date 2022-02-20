<?php

namespace TheClinic\Order\Laser\Calculations;

use TheClinic\DataStructures\Order\DSPackages;
use TheClinic\DataStructures\Order\DSParts;

trait TraitCollectDistinguishedParts
{
    /**
     * It collects parts from $packages that don't already exist in $parts and then adds them to $parts 
     * so we can have all the unique parts in $parts and $packages.
     *
     * @param \TheClinic\DataStructures\Order\DSParts $parts
     * @param \TheClinic\DataStructures\Order\DSPackages $packages
     * @return array
     */
    private function collectDistinguishedParts(DSParts $parts, DSPackages $packages): array
    {
        $packagesParts = [];

        /** @var \TheClinic\DataStructures\Order\DSPackage $package */
        foreach ($packages as $package) {
            /** @var \TheClinic\DataStructures\Order\DSPart $packagePart */
            foreach ($package->getParts() as $packagePart) {
                $found = true;
                /** @var \TheClinic\DataStructures\Order\DSPart $part */
                foreach ($parts as $part) {
                    if ($part->getId() === $packagePart->getId()) {
                        continue;
                    }

                    $found = false;
                }

                if (!$found) {
                    $packagesParts[] = $packagePart;
                }
            }
        }

        foreach ($parts as $part) {
            array_push($packagesParts, $part);
        }

        return $packagesParts;
    }
}
