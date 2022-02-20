<?php

namespace TheClinic\DataStructures\Order;

use TheClinic\DataStructures\Traits\TraitKeyPositioner;
use TheClinic\DataStructures\User\DSUser;
use TheClinic\Exceptions\DataStructures\NoKeyFoundException;
use TheClinic\Exceptions\DataStructures\Order\InvalidOffsetTypeException;
use TheClinic\Exceptions\DataStructures\Order\InvalidUserException;
use TheClinic\Exceptions\DataStructures\Order\InvalidValueTypeException;

class DSOrders implements \ArrayAccess, \Iterator, \Countable
{
    use TraitKeyPositioner;

    public DSUser|null $user;

    /**
     * @var \TheClinic\DataStructures\Order\DSOrder[]
     */
    private array $orders;

    private int $position;

    public function __construct(DSUser|null $user = null)
    {
        $this->user = $user;
        $this->position = 0;
    }

    // -------------------- \ArrayAccess

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->visits[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        if (gettype($offset) !== "integer") {
            throw new InvalidOffsetTypeException("This data structure only accepts integer as an offset type.", 500);
        }

        return $this->visits[$offset];
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        if (gettype($offset) !== "integer" && !is_null($offset)) {
            throw new InvalidOffsetTypeException("This data structure only accepts integer and null as an offset type.", 500);
        }

        if (!($value instanceof DSOrder)) {
            throw new InvalidValueTypeException("This data structure only accepts the type: " . DSOrder::class . " as an array member.", 500);
        }

        if (isset($this->user) && !is_null($this->user) && $this->user->getId() !== $value->user->getId()) {
            throw new InvalidUserException("The members of this data structure must belong to the same specified user. Mismatched member id: " . $value->getId(), 500);
        }

        if (is_null($offset)) {
            $this->orders[] = $value;
        } elseif (gettype($offset) === "integer") {
            $this->orders[$offset] = $value;
        }
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->orders[$offset]);
    }

    // -------------------- \Iterator

    public function current(): mixed
    {
        return $this->orders[$this->position];
    }

    public function key(): mixed
    {
        return $this->position;
    }

    public function next(): void
    {
        if (($lastKey = array_key_last($this->orders)) === null) {
            $this->position++;
            return;
        }

        try {
            $this->position = $this->findNextPosition(function ($offset) {
                return isset($this->orders[$offset]);
            }, $this->position, $lastKey);
        } catch (NoKeyFoundException $th) {
            $this->position++;
        }
    }

    public function rewind(): void
    {
        $this->position = 0;
    }

    public function valid(): bool
    {
        return isset($this->orders[$this->position]);
    }

    // ------------------------------------ \Countable

    public function count(): int
    {
        return count($this->orders);
    }

    // ------------------------------------------------------------------------------------
}
