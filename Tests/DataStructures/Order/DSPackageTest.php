<?php

namespace Tests\DataStructures\Order;

use Faker\Factory;
use Faker\Generator;
use Mockery;
use Tests\TestCase;
use TheClinic\DataStructures\Order\DSPackage;
use TheClinic\DataStructures\Order\DSParts;
use TheClinic\Exceptions\DataStructures\Order\OrderExceptions;

class DSPackageTest extends TestCase
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
        $name = $faker->lexify();
        $gender = "Male";
        $price = $faker->numberBetween(50000, 600000);
        $createdAt = new \DateTime($faker->time("Y-m-d H:i:s"));
        $updatedAt = new \DateTime($faker->time("Y-m-d H:i:s"));

        /** @var \TheClinic\DataStructures\Order\DSParts|\Mockery\MockInterface $parts */
        $parts = Mockery::mock(DSParts::class);
        $parts->shouldReceive("getGender")->andReturn($gender);

        $this->runTheAssertions(
            $id,
            $name,
            $gender,
            $price,
            $parts,
            $createdAt,
            $updatedAt
        );

        /** @var \TheClinic\DataStructures\Order\DSParts|\Mockery\MockInterface $parts */
        $parts = Mockery::mock(DSParts::class);
        $parts->shouldReceive("getGender")->andReturn("Female");

        try {
            $this->runTheAssertions(
                $id,
                $name,
                $gender,
                $price,
                $parts,
                $createdAt,
                $updatedAt
            );
        } catch (OrderExceptions $th) {
        }
    }

    private function runTheAssertions(
        int $id,
        string $name,
        string $gender,
        int $price,
        DSParts $parts,
        \DateTime $createdAt,
        \DateTime $updatedAt
    ): void {
        $dsPart = new DSPackage(
            $id,
            $name,
            $gender,
            $price,
            $parts,
            $createdAt,
            $updatedAt
        );

        $this->assertEquals($dsPart->getId(), $id);
        $this->assertEquals($dsPart->getName(), $name);
        $this->assertEquals($dsPart->getGender(), $gender);
        $this->assertEquals($dsPart->getPrice(), $price);
        $this->assertEquals($dsPart->getParts(), $parts);
        $this->assertEquals($dsPart->getCreatedAt()->getTimestamp(), $createdAt->getTimestamp());
        $this->assertEquals($dsPart->getUpdatedAt()->getTimestamp(), $updatedAt->getTimestamp());
    }
}
