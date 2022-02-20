<?php

namespace TheClinic\DataStructures\Time;

use TheClinic\DataStructures\Traits\TraitKeyPositioner;
use TheClinic\Exceptions\DataStructures\NoKeyFoundException;
use TheClinic\Exceptions\DataStructures\Time\InvalidOffsetTypeException;
use TheClinic\Exceptions\DataStructures\Time\InvalidValueTypeException;
use TheClinic\Exceptions\DataStructures\Time\TimeSequenceViolationException;

class DSTimePeriods implements \Countable, \Iterator, \ArrayAccess
{
    use TraitKeyPositioner;

    /**
     * @var \TheClinic\DataStructures\Time\DSTimePeriod[]
     */
    private array $dsTimePeriods = [];

    private int $position;

    public function cloneIt(): DSTimePeriods
    {
        $dsDTimePeriods = new DSTimePeriods();

        foreach ($this->dsTimePeriods as $dsTimePeriod) {
            $dsDTimePeriods[] = $dsTimePeriod->cloneIt();
        }

        return $dsDTimePeriods;
    }

    // ------------------------------------ \Countable

    public function count(): int
    {
        return count($this->dsTimePeriods);
    }

    // ------------------------------------ \Iterator

    public function current(): mixed
    {
        return $this->dsTimePeriods[$this->position];
    }

    public function key(): mixed
    {
        return $this->position;
    }

    public function next(): void
    {
        if (($lastKey = array_key_last($this->dsTimePeriods)) === null) {
            $this->position++;
            return;
        }

        try {
            $this->position = $this->findNextPosition(function ($offset) {
                return isset($this->dsTimePeriods[$offset]);
            }, $this->key(), $lastKey);
        } catch (NoKeyFoundException $th) {
            $this->position++;
        }
    }

    public function prev(): void
    {
        try {
            $this->position = $this->findPreviousPosition(function ($offset) {
                return isset($this->dsTimePeriods[$offset]);
            }, $this->key());
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
        return isset($this->dsTimePeriods[$this->position]);
    }

    // ------------------------------------ \ArrayAccess

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->dsTimePeriods[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->dsTimePeriods[$offset];
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->validateInsertingValue($value);

        if ($this->offsetExists($offset)) {
            $this->offsetUnset($offset);
        }

        if (gettype($offset) === "integer") {
            $this->validateInsert($offset, $value);
            $this->dsTimePeriods[$offset] = $value;
        } elseif (is_null($offset)) {
            $this->validateInsert($offset, $value);
            $this->dsTimePeriods[] = $value;
        } else {
            throw new InvalidOffsetTypeException("Only Integer and null are the acceptable types for the offset.", 500);
        }
    }

    public function offsetUnset(mixed $offset): void
    {
        if (!$this->offsetExists($offset)) {
            return;
        }

        unset($this->dsTimePeriods[$offset]);
    }

    // --------------------------------------------------------------------

    private function validateInsertingValue(mixed $value): void
    {
        if (gettype($value) === "object" && $value instanceof DSTimePeriod) {
            return;
        }

        throw new InvalidValueTypeException("The new insert must be of type: " . DSTimePeriod::class . ".", 500);
    }

    private function validateInsert(int|null $offset, DSTimePeriod $value): void
    {
        if (count($this->dsTimePeriods) === 0) {
            return;
        }

        if (
            is_null($offset) &&
            $this->dsTimePeriods[array_key_last($this->dsTimePeriods)]->getEndTimestamp() < $value->getStartTimestamp()
        ) {
            return;
        } elseif (is_null($offset)) {
            throw new TimeSequenceViolationException("The new insert doesn't respect the order of older time period.", 500);
        }

        try {
            if (($lastKey = array_key_last($this->dsTimePeriods)) !== null) {
                $nextKey = $this->findNextPosition([$this, "offsetExists"], $offset, $lastKey);
            }
        } catch (NoKeyFoundException $th) {
        }

        try {
            $previousKey = $this->findPreviousPosition([$this, "offsetExists"], $offset);
        } catch (NoKeyFoundException $th) {
        }

        if (isset($nextKey) && isset($previousKey)) {
            if (
                $value->getStartTimestamp() > $this->dsTimePeriods[$previousKey]->getEndTimestamp() &&
                $value->getEndTimestamp() < $this->dsTimePeriods[$nextKey]->getStartTimestamp()
            ) {
                return;
            }
        } elseif (isset($nextKey)) {
            if ($value->getEndTimestamp() < $this->dsTimePeriods[$nextKey]->getStartTimestamp()) {
                return;
            }
        } elseif (isset($previousKey)) {
            if ($value->getStartTimestamp() > $this->dsTimePeriods[$previousKey]->getEndTimestamp()) {
                return;
            }
        }

        throw new TimeSequenceViolationException("The new insert doesn't respect the order of older time period.", 500);
    }
}
