<?php

namespace Tests\Fakers\Order;

use Faker\Factory;
use Faker\Generator;
use TheClinicDataStructures\DataStructures\Order\DSPart;

class DSPartFaker
{
    private Generator $faker;

    private null|int $id;

    private null|string $name;

    private null|string $gender;

    private null|int $price;

    private null|int $neededTime;

    private null|\DateTime $createdAt;

    private null|\DateTime $updatedAt;

    public function __construct(
        null|int $id = null,
        null|string $name = null,
        null|string $gender = null,
        null|int $price = null,
        null|int $neededTime = null,
        null|\DateTime $createdAt = null,
        null|\DateTime $updatedAt = null
    ) {
        $this->faker = Factory::create();

        $this->id = $id;
        $this->name = $name;
        $this->gender = $gender;
        $this->price = $price;
        $this->neededTime = $neededTime;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    public function fakeIt(): DSPart
    {
        return new DSPart(
            $this->id !== null ? $this->id : $this->faker->numberBetween(1, 1000),
            $this->name !== null ? $this->name : $this->faker->lexify(),
            $this->gender !== null ? $this->gender : $this->faker->randomElement(["Female", "Male"]),
            $this->price !== null ? $this->price : $this->faker->numberBetween(6000000, 20000000),
            $this->neededTime !== null ? $this->neededTime : $this->faker->numberBetween(600, 3600),
            $this->createdAt !== null ? $this->createdAt : new \DateTime(),
            $this->updatedAt !== null ? $this->updatedAt : new \DateTime(),
        );
    }
}
