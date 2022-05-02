<?php

namespace Tests\Fakers\Order;

use Faker\Factory;
use Faker\Generator;
use TheClinicDataStructures\DataStructures\Order\DSPackage;
use TheClinicDataStructures\DataStructures\Order\DSParts;

class DSPackageFaker
{
    private Generator $faker;

    private int $id;

    private null|string $name;

    private null|string $gender;

    private null|int $price;

    private null|DSParts $parts;

    private null|int $partsCount;

    private null|\DateTime $createdAt;

    private null|\DateTime $updatedAt;

    public function __construct(
        null|int $id = null,
        null|string $name = null,
        null|string $gender = null,
        null|int $price = null,
        null|DSParts $parts = null,
        null|int $partsCount = null,
        null|\DateTime $createdAt = null,
        null|\DateTime $updatedAt = null
    ) {
        $this->faker = Factory::create();

        $this->id = $id;
        $this->name = $name;
        $this->gender = $gender;
        $this->price = $price;
        $this->parts = $parts;
        $this->partsCount = $partsCount;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    public function fakeIt(): DSPackage
    {
        return new DSPackage(
            $this->id !== null ? $this->id : $this->faker->numberBetween(1, 1000),
            $this->name !== null ? $this->name : $this->faker->lexify(),
            $this->gender !== null ? $this->gender : ($this->gender = $this->faker->randomElement(["Female", "Male"])),
            $this->price !== null ? $this->price : $this->faker->numberBetween(6000000, 20000000),
            $this->parts !== null ? $this->parts : (new DSPartsFaker($this->gender, null, $this->partsCount ?: $this->faker->numberBetween(1, 10)))->fakeIt(),
            $this->createdAt !== null ? $this->createdAt : new \DateTime(),
            $this->updatedAt !== null ? $this->updatedAt : new \DateTime(),
        );
    }
}
