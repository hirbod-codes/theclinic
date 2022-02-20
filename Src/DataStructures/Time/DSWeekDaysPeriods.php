<?php

namespace TheClinic\DataStructures\Time;

use TheClinic\DataStructures\Traits\TraitKeyPositioner;
use TheClinic\Exceptions\DataStructures\NoKeyFoundException;
use TheClinic\Exceptions\DataStructures\Time\InvalidOffsetTypeException;
use TheClinic\Exceptions\DataStructures\Time\InvalidValueTypeException;

class DSWeekDaysPeriods implements \Iterator, \Countable, \ArrayAccess
{
    use TraitKeyPositioner;

    public static array $weekDays = ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday"];

    public array $sortedWeekDays;

    public bool $notEmpty = false;

    protected DSTimePeriods $Monday;

    protected DSTimePeriods $Tuesday;

    protected DSTimePeriods $Wednesday;

    protected DSTimePeriods $Thursday;

    protected DSTimePeriods $Friday;

    protected DSTimePeriods $Saturday;

    protected DSTimePeriods $Sunday;

    private int $position;

    private string $startingDay;

    public function __construct(string $startingDay)
    {
        $this->position = 0;
        $this->startingDay = $startingDay;
        $this->sortedWeekDays = $this->sortWeekDays($this->startingDay);
    }

    public function setStartingDay(string $dayOfWeek): void
    {
        $this->startingDay = $dayOfWeek;
        $this->sortedWeekDays = $this->sortWeekDays($this->startingDay);
    }

    public function getStartingDay(): string
    {
        return $this->startingDay;
    }

    private function sortWeekDays($startingDay): array
    {
        $sortedWeekDays = [];

        $pass = false;
        foreach (self::$weekDays as $weekDay) {
            if (!$pass) {
                if ($weekDay === $startingDay) {
                    $pass = true;
                }
                if (!$pass) {
                    continue;
                }
            }

            $sortedWeekDays[] = $weekDay;
        }

        foreach (array_diff(self::$weekDays, $sortedWeekDays) as $day) {
            $sortedWeekDays[] = $day;
        }

        return $sortedWeekDays;
    }

    private function validateValue(mixed $value): void
    {
        if (!($value instanceof DSTimePeriods)) {
            throw new InvalidValueTypeException("The new inserting value must be of type " . DSTimePeriods::class . " data structure.", 500);
        }
    }

    private function validateOffset(mixed $offset): void
    {
        if (!is_string($offset) && !is_int($offset)) {
            throw new InvalidOffsetTypeException("Only string and integer are the accepted types for \$offset.", 500);
        } elseif (is_string($offset) && !in_array($offset, self::$weekDays)) {
            throw new InvalidOffsetTypeException("String offset must be one of the followings:" . implode(", ", self::$weekDays) . ".", 500);
        } elseif (is_int($offset) && $offset > 6) {
            throw new InvalidOffsetTypeException("The offset must be equal or less than 6.", 500);
        }
    }

    public function cloneIt(): self
    {
        $newDSWorkSchdule = new DSWorkSchedule($this->startingDay);

        foreach (DSWorkSchedule::$weekDays as $weekDay) {
            $newDSWorkSchdule[$weekDay] = $this->{$weekDay}->cloneIt();
            
            // /** @var DSTimePeriod $dsTimePeriod */
            // foreach ($this->{$weekDay} as $dsTimePeriod) {
            //     $newDSWorkSchdule[$weekDay][] = new DSTimePeriod($dsTimePeriod->getStart(), $dsTimePeriod->getEnd());
            // }
        }

        return $newDSWorkSchdule;
    }

    // ------------------------------------ \Countable

    public function count(): int
    {
        $count = 0;
        foreach (self::$weekDays as $day) {
            if (isset($this->{$day})) {
                $count++;
            }
        }

        return $count;
    }

    // ------------------------------------ \Iterator

    public function current(): mixed
    {
        return $this->{$this->sortedWeekDays[$this->position]};
    }

    public function key(): mixed
    {
        return $this->sortedWeekDays[$this->position];
    }

    public function next(): void
    {
        if (!$this->notEmpty) {
            $this->position++;
            return;
        }

        try {
            $this->position = $this->findNextPosition(function ($offset) {
                return isset($this->sortedWeekDays[$offset]) && isset($this->{$this->sortedWeekDays[$offset]});
            }, $this->position, array_key_last($this->sortedWeekDays));
        } catch (NoKeyFoundException $th) {
            $this->position++;
        }
    }

    public function prev(): void
    {
        if (!$this->notEmpty) {
            $this->position--;
            return;
        }

        try {
            $this->position = $this->findPreviousPosition(function ($offset) {
                return isset($this->sortedWeekDays[$offset]) && isset($this->{$this->sortedWeekDays[$offset]});
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
        return isset($this->sortedWeekDays[$this->position]) && isset($this->{$this->sortedWeekDays[$this->position]});
    }

    // ------------------------------------ \ArrayAccess

    public function offsetExists(mixed $offset): bool
    {
        $this->validateOffset($offset);

        if (is_string($offset) && !is_numeric($offset)) {
            return isset($this->{$offset});
        }

        return isset($this->{$this->sortedWeekDays[intval($offset)]});
    }

    public function offsetGet(mixed $offset): mixed
    {
        $this->validateOffset($offset);

        if (is_int($offset)) {
            return $this->{$this->sortedWeekDays[$offset]};
        }

        return $this->{$offset};
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->validateValue($value);

        $this->validateOffset($offset);

        if (is_string($offset)) {
            $this->{$offset} = $value;
        } elseif (is_int($offset)) {
            $this->{$this->sortedWeekDays[$offset]} = $value;
        }

        $this->notEmpty = true;
    }

    public function offsetUnset(mixed $offset): void
    {
        if ($this->offsetExists($offset)) {
            if (is_string($offset)) {
                unset($this->{$offset});
            } else {
                unset($this->{$this->sortedWeekDays[$offset]});
            }
        }
    }
}
