<?php

namespace Tests\DataStructures\Order;

use Faker\Factory;
use Faker\Generator;
use Tests\TestCase;
use TheClinic\DataStructures\Order\DSPart;

class DSPartTest extends TestCase
{
    private Generator $faker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->faker = Factory::create();
    }

    public function testDataStructure(): void
    {
        $faker = $this->faker;

        $id = $faker->numberBetween(1, 100);
        $gender = $faker->randomElement(["Male", "Female"]);
        $name = $faker->name($gender);
        $price = $faker->numberBetween(50000, 600000);
        $neededTime = $faker->numberBetween(600, 7200);
        $createdAt = new \DateTime($faker->time("Y-m-d H:i:s"));
        $updatedAt = new \DateTime($faker->time("Y-m-d H:i:s"));

        $dsPart = new DSPart(
            $id,
            $name,
            $gender,
            $price,
            $neededTime,
            $createdAt,
            $updatedAt
        );

        $this->assertEquals($dsPart->getId(), $id);
        $this->assertEquals($dsPart->getName(), $name);
        $this->assertEquals($dsPart->getGender(), $gender);
        $this->assertEquals($dsPart->getPrice(), $price);
        $this->assertEquals($dsPart->getNeededTime(), $neededTime);
        $this->assertEquals($dsPart->getCreatedAt()->getTimestamp(), $createdAt->getTimestamp());
        $this->assertEquals($dsPart->getUpdatedAt()->getTimestamp(), $updatedAt->getTimestamp());
    }
}
