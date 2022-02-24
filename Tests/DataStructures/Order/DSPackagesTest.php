<?php

namespace Tests\DataStructures\Order;

use Faker\Factory;
use Faker\Generator;
use Mockery;
use Tests\TestCase;
use TheClinic\DataStructures\Order\DSPackage;
use TheClinic\DataStructures\Order\DSPackages;
use TheClinic\Exceptions\DataStructures\Order\InvalidGenderException;

class DSPackagesTest extends TestCase
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

        $packages = $this->makePackages($gender, 10);

        $this->runTheAssertions($gender, $packages);

        /** @var \TheClinic\DataStructures\Order\DSPackage|\Mockery\MockInterface $package */
        $package = Mockery::mock(DSPackage::class);
        $package->shouldReceive("getId")->andReturn($this->faker->numberBetween(1, 1000));
        $package->shouldReceive("getGender")->andReturn("Female");
        $packages[3] = $package;

        try {
            $this->runTheAssertions($gender, $packages);
        } catch (InvalidGenderException $th) {
        }
    }

    private function makePackages(string $gender, int $count): array
    {
        $packages = [];

        for ($i = 0; $i < $count; $i++) {
            /** @var \TheClinic\DataStructures\Order\DSPackage|\Mockery\MockInterface $package */
            $package = Mockery::mock(DSPackage::class);
            $package->shouldReceive("getId")->andReturn($this->faker->numberBetween(1, 1000));
            $package->shouldReceive("getGender")->andReturn($gender);

            $packages[] = $package;
        }

        return $packages;
    }

    private function runTheAssertions(string $gender, array $packages): void
    {
        $dsPackages = new DSPackages($gender);
        $this->assertEquals($dsPackages->getGender(), $gender);

        foreach ($packages as $package) {
            $dsPackages[] = $package;
        }
        $this->assertCount(count($packages), $dsPackages);

        for ($i = 0; $i < count($packages); $i++) {
            $dsPackages[$i] = $packages[$i];
        }
        $this->assertCount(count($packages), $dsPackages);

        unset($dsPackages[4]);

        $counter = 0;
        /** @var \TheClinic\DataStructures\Order\DSPackage $package */
        foreach ($dsPackages as $package) {
            $this->assertInstanceOf(DSPackage::class, $package);

            $this->assertEquals($package->getGender(), $dsPackages->getGender());

            $counter++;
        }

        $this->assertEquals(count($packages) - 1, $counter);
    }
}
