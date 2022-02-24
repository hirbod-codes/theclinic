<?php

namespace Tests\DataStructures\Order;

use Faker\Factory;
use Faker\Generator;
use Mockery;
use Tests\TestCase;
use TheClinic\DataStructures\Order\DSPart;
use TheClinic\DataStructures\Order\DSParts;
use TheClinic\Exceptions\DataStructures\Order\OrderExceptions;

class DSPartsTest extends TestCase
{
    private Generator $faker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->faker = Factory::create();
    }

    public function testDataStructure(): void
    {
        $gender = "Male";

        $parts = $this->makeParts($gender, 10);

        $this->runTheAssertions($gender, $parts);

        /** @var \TheClinic\DataStructures\Order\DSPart|\Mockery\MockInterface $part */
        $part = Mockery::mock(DSPart::class);
        $part->shouldReceive("getGender")->andReturn("Female");
        $parts[3] = $part;

        try {
            $this->runTheAssertions($gender, $parts);
        } catch (OrderExceptions $th) {
        }
    }

    private function makeParts(string $gender, int $partCount): array
    {
        $parts = [];
        for ($i = 0; $i < $partCount; $i++) {
            /** @var \TheClinic\DataStructures\Order\DSPart|\Mockery\MockInterface $part */
            $part = Mockery::mock(DSPart::class);
            $part->shouldReceive("getGender")->andReturn($gender);

            $parts[] = $part;
        }

        return $parts;
    }

    private function runTheAssertions(string $gender, array $parts): void
    {
        $dsParts = new DSParts($gender);
        $this->assertEquals($dsParts->getGender(), $gender);

        foreach ($parts as $part) {
            $dsParts[] = $part;
        }
        $this->assertCount(count($parts), $dsParts);

        $dsParts = new DSParts($gender);
        $this->assertEquals($dsParts->getGender(), $gender);

        for ($i = 0; $i < count($parts); $i++) {
            $dsParts[$i] = $parts[$i];
        }
        $this->assertCount(count($parts), $dsParts);

        unset($dsParts[4]);

        $counter = 0;
        /** @var \TheClinic\DataStructures\Order\DSPart $part */
        foreach ($dsParts as $part) {
            $this->assertInstanceOf(DSPart::class, $part);

            $this->assertEquals($part->getGender(), $dsParts->getGender());

            $counter++;
        }

        $this->assertEquals(count($parts) - 1, $counter);
    }
}
