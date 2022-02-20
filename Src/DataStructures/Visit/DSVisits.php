<?php

namespace TheClinic\DataStructures\Visit;

use TheClinic\DataStructures\Order\DSOrder;
use TheClinic\DataStructures\Traits\TraitKeyPositioner;
use TheClinic\DataStructures\User\DSUser;
use TheClinic\Exceptions\DataStructures\NoKeyFoundException;
use TheClinic\Exceptions\DataStructures\Visit\InvalidOffsetTypeException;
use TheClinic\Exceptions\DataStructures\Visit\InvalidValueException;
use TheClinic\Exceptions\DataStructures\Visit\InvalidValueTypeException;
use TheClinic\Exceptions\DataStructures\Visit\TimeSequenceViolationException;

class DSVisits implements \ArrayAccess, \Iterator, \Countable
{
    use TraitKeyPositioner;

    public DSUser|null $user;

    public DSOrder|null $order;

    /**
     * @var \TheClinic\DataStructures\Visit\DSVisit[]
     */
    private array $visits;

    /**
     * position of the pointer of this data structure.(as we use it as a Iterable object)
     *
     * @var integer
     */
    private int $position;

    /**
     * The order in which the visits are sorted.
     *
     * @var string
     */
    private string $sort;

    public function __construct(string $sort = "sort", DSUser|null $user = null, DSOrder|null $order = null)
    {
        $this->user = $user;
        $this->order = $order;

        $this->visits = [];
        $this->position = 0;

        $this->sort = $sort;
    }

    public function getSort(): string
    {
        return $this->sort;
    }

    public function setSort(string $sort): void
    {
        $values = ["ASC", "DESC", "Natural"];
        if (!in_array($sort, $values, true)) {
            throw new InvalidValueException("\$sort value must be one of the following: " . implode(", ", $values) . ".", 500);
        }

        $this->sort = $sort;
    }

    // -------------------- \ArrayAccess

    public function offsetExists(mixed $offset): bool
    {
        if (!is_int($offset)) {
            throw new InvalidOffsetTypeException("This data structure only accepts integer as an offset type.", 500);
        }

        return isset($this->visits[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        if (!is_int($offset)) {
            throw new InvalidOffsetTypeException("This data structure only accepts integer as an offset type.", 500);
        }

        return $this->visits[$offset];
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        if (!($value instanceof DSVisit)) {
            throw new InvalidValueTypeException("The new member must be an object of class: " . DSVisit::class, 500);
        }

        if (!is_null($offset) && !is_int($offset)) {
            throw new InvalidOffsetTypeException("This data structure only accepts integer as an offset type.", 500);
        }

        switch ($this->sort) {
            case 'ASC':
                if (is_null($offset)) {
                    $this->handleAscendingNullOffset($this->visits, $value);
                } else {
                    $this->handleAscendingIntegerOffset($this->visits, $offset, $value);
                }
                break;

            case 'DESC':
                if (is_null($offset)) {
                    $this->handleDescendingNullOffset($this->visits, $value);
                } else {
                    $this->handleDescendingIntegerOffset($this->visits, $offset, $value);
                }
                break;

            case 'Natural':
                $this->handleNatural($this->visits, $offset, $value);
                break;

            default:
                break;
        }

        if (is_null($offset)) {
            $this->visits[] = $value;
        } elseif (gettype($offset) === "integer") {
            if ($this->offsetExists($offset)) {
                $this->offsetUnset($offset);
            }

            $this->visits[$offset] = $value;

            ksort($this->visits, SORT_NUMERIC);
        }
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->visits[$offset]);
    }

    // -------------------- \Iterator

    public function current(): mixed
    {
        return $this->visits[$this->position];
    }

    public function key(): mixed
    {
        return $this->position;
    }

    public function next(): void
    {
        if (($lastKey = array_key_last($this->visits)) === null) {
            $this->position++;
            return;
        }

        try {
            $this->position = $this->findNextPosition(function ($offset) {
                return isset($this->visits[$offset]);
            }, $this->position, $lastKey);
        } catch (NoKeyFoundException $th) {
            $this->position++;
        }
    }

    public function prev(): void
    {
        if (count($this->visits) === 0) {
            $this->position--;
            return;
        }

        try {
            $this->position = $this->findPreviousPosition(function ($offset) {
                return isset($this->visits[$offset]);
            }, $this->position);
        } catch (NoKeyFoundException $th) {
            $this->position--;
        }
    }

    public function rewind(): void
    {
        $this->position = 0;
    }

    public function valid(): bool
    {
        return isset($this->visits[$this->position]);
    }

    // ------------------------------------ \Countable

    public function count(): int
    {
        return count($this->visits);
    }

    // ------------------------------------------------------------------------------------ 

    private function handleAscendingNullOffset(array $visits, DSVisit $visit): void
    {
        if (count($visits) === 0) {
            return;
        }

        $lastVisit = $visits[array_key_last($visits)];

        if (!$this->compare($lastVisit, $visit)) {
            throw new TimeSequenceViolationException("The new member doesn't respect the order of array members.", 500);
        }
    }

    private function handleAscendingIntegerOffset(array $visits, int $offset, DSVisit $visit): void
    {
        try {
            $previousKey = $this->findPreviousPosition([$this, "offsetExists"], $offset);
        } catch (NoKeyFoundException $th) {
        }

        try {
            if (($lastKey = array_key_last($visits)) !== null) {
                $nextKey = $this->findNextPosition([$this, "offsetExists"], $offset, $lastKey);
            }
        } catch (NoKeyFoundException $th) {
        }

        if (isset($nextKey) && isset($previousKey)) {
            $pastVisit = $visits[$previousKey];
            $nextVisit = $visits[$nextKey];

            if ($this->compare($pastVisit, $visit) && $this->compare($visit, $nextVisit)) {
                return;
            }
        } elseif (isset($previousKey)) {
            $pastVisit = $visits[$previousKey];

            if ($this->compare($pastVisit, $visit)) {
                return;
            }
        } elseif (isset($nextKey)) {
            $nextVisit = $visits[$nextKey];

            if ($this->compare($visit, $nextVisit)) {
                return;
            }
        } else {
            return;
        }

        throw new TimeSequenceViolationException("The new member doesn't respect the order of array members.", 500);
    }

    private function handleDescendingNullOffset(array $visits, DSVisit $visit): void
    {
        if (count($visits) === 0) {
            return;
        }

        $lastVisit = $visits[array_key_last($visits)];

        if (!$this->compare($visit, $lastVisit)) {
            throw new TimeSequenceViolationException("The new member doesn't respect the order of array members.", 500);
        }
    }

    private function handleDescendingIntegerOffset(array $visits, int $offset, DSVisit $visit): void
    {
        try {
            $previousKey = $this->findPreviousPosition([$this, "offsetExists"], $offset);
        } catch (NoKeyFoundException $th) {
        }

        try {
            if (($lastKey = array_key_last($visits)) !== null) {
                $nextKey = $this->findNextPosition([$this, "offsetExists"], $offset, $lastKey);
            }
        } catch (NoKeyFoundException $th) {
        }

        if (isset($nextKey) && isset($previousKey)) {
            $pastVisit = $visits[$previousKey];
            $nextVisit = $visits[$nextKey];

            if ($this->compare($visit, $pastVisit) && $this->compare($nextVisit, $visit)) {
                return;
            }
        } elseif (isset($previousKey)) {
            $pastVisit = $visits[$previousKey];

            if ($this->compare($visit, $pastVisit)) {
                return;
            }
        } elseif (isset($nextKey)) {
            $nextVisit = $visits[$nextKey];

            if ($this->compare($nextVisit, $visit)) {
                return;
            }
        } else {
            return;
        }

        throw new TimeSequenceViolationException("The new member doesn't respect the order of array members.", 500);
    }

    private function handleNatural(array $visits, int|null $offset, DSVisit $visit): void
    {
        $error = false;
        foreach ($visits as $key => $oldVisit) {
            if (!is_null($offset) && $key === $offset) {
                continue;
            }

            if (
                $visit->getVisitTimestamp() < ($oldVisit->getVisitTimestamp() + $oldVisit->getConsumingTime()) &&
                ($visit->getVisitTimestamp() + $visit->getConsumingTime()) > $oldVisit->getVisitTimestamp()
            ) {
                $error = true;
            }
        }

        if ($error) {
            throw new TimeSequenceViolationException("The new member doesn't respect the order of array members.", 500);
        }

        unset($this->visits[$offset]);
    }

    private function compare(DSVisit $previousVisit, DSVisit $nextVisit): bool
    {
        return $nextVisit->getVisitTimestamp() >= ($previousVisit->getVisitTimestamp() + $previousVisit->getConsumingTime());
    }

    public function findLastPosition(): int
    {
        return array_key_last($this->visits);
    }
}
