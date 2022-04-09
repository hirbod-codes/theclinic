<?php

namespace TheClinic\Visit\Utilities;

use TheClinic\Exceptions\Visit\NeededTimeOutOfRange;
use TheClinic\Exceptions\Visit\VisitSearchFailure;

class SearchBetweenTimestamps
{
    public function search(int $startTS, int $endTS, int $neededTime, \ArrayAccess|\Countable $arrayAccess, array|\Closure $getItemStartTS, array|\Closure $getItemEndTS): \Generator
    {
        $this->validateArrayAccess($arrayAccess);
        $this->validateTimestamps($startTS, $endTS, $neededTime);
        $this->confirmIntReturnType($getItemEndTS);
        $this->confirmIntReturnType($getItemStartTS);

        if (count($arrayAccess) === 0) {
            return yield [$startTS, $endTS];
        }

        for ($i = 0; $i < count($arrayAccess); $i++) {
            (new \ReflectionFunction($getItemStartTS))->getReturnType();
            $itemStartTS = call_user_func($getItemStartTS, $arrayAccess[$i]);
            $itemEndTS = call_user_func($getItemEndTS, $arrayAccess[$i]);

            if ($itemStartTS < $startTS && $itemEndTS > $endTS) {
                throw new NeededTimeOutOfRange('', 500);
            }

            if ($itemEndTS <= $startTS) {
                continue;
            } elseif ($itemStartTS <= $startTS) {
                $startTS = $itemEndTS;
                $this->validateTimestamps($startTS, $endTS, $neededTime);
                continue;
            }

            if (isset($previousItemEndTS)) {
                $previousBlock = $previousItemEndTS;
            } else {
                $previousBlock = $startTS;
            }

            if ($itemStartTS > $endTS) {
                $currentBlock = $endTS;
            } else {
                $currentBlock = $itemStartTS;
            }

            if (
                $previousBlock >= $startTS &&
                $currentBlock <= $endTS &&
                ($currentBlock - $previousBlock) >= $neededTime
            ) {
                yield [$previousBlock, $currentBlock];
            }

            $previousItemEndTS = $itemEndTS;

            if ($itemEndTS > $endTS) {
                break;
            }
        }

        if ($itemEndTS < $endTS) {
            unset($previousBlock);
            if ($itemEndTS >= $startTS && ($endTS - $itemEndTS) >= $neededTime) {
                $previousBlock = $itemEndTS;
            } elseif ($itemEndTS <= $startTS) {
                $previousBlock = $startTS;
            }

            if (isset($previousBlock)) {
                yield [$previousBlock, $endTS];
            }
        }
    }

    private function validateArrayAccess(\ArrayAccess|\Countable $arrayAccess): void
    {
        if (
            !($arrayAccess instanceof \ArrayAccess) ||
            !($arrayAccess instanceof \Countable)
        ) {
            throw new \InvalidArgumentException('The varable $arrayAccess doesn\'t implement required interfaces.', 500);
        }
    }

    private function validateTimestamps(int $startTS, int $endTS, int $neededTime): void
    {
        if ($endTS <= $startTS) {
            throw new VisitSearchFailure("Failed to find a visit in requested time range.", 500);
        }

        if (($endTS - $startTS) < $neededTime) {
            throw new NeededTimeOutOfRange('', 500);
        }
    }

    private function confirmIntReturnType(array|\Closure $callback): void
    {
        $type = (new \ReflectionFunction($callback))->getReturnType();

        if (
            ($type instanceof \ReflectionNamedType && $type->getName() !== 'int') ||
            ($type instanceof \ReflectionUnionType) ||
            ($type instanceof \ReflectionIntersectionType)
        ) {
            throw new \InvalidArgumentException('The provided callbacks must have integer as their return type.', 500);
        }
    }
}
