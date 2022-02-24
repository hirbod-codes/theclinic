<?php

namespace Tests\Order\Laser\Calculations;

use Faker\Factory;
use Faker\Generator;
use Mockery;
use Tests\TestCase;
use Tests\Fakers\Order\DSPackageFaker;
use Tests\Fakers\Order\DSPackagesFaker;
use Tests\Fakers\Order\DSPartFaker;
use Tests\Fakers\Order\DSPartsFaker;
use TheClinic\DataStructures\Order\DSPackages;
use TheClinic\DataStructures\Order\DSPart;
use TheClinic\DataStructures\Order\DSParts;
use TheClinic\Order\Laser\Calculations\PriceCalculator;

class PriceCalculatorTest extends TestCase
{
    private Generator $faker;

    private DSParts $dsParts;

    private DSPackages $dsPackages;

    protected function setUp(): void
    {
        parent::setUp();
        $this->faker = Factory::create();

        $gender = "Male";

        $this->dsParts = (new DSPartsFaker($gender, $this->makeParts(3, $gender, 10, 600)))->fakeIt();

        $this->dsPackages = (new DSPackagesFaker($gender, $this->makePackages(2, 3, $gender, 20, 10, 600)))->fakeIt();
    }

    public function testCalculate(): void
    {
        $result = (new PriceCalculator)->calculate($this->dsParts, $this->dsPackages);

        $this->assertEquals(70, $result);
    }

    public function testCalculateWithoutDiscount(): void
    {
        $result = (new PriceCalculator)->calculateWithoutDiscount($this->dsParts, $this->dsPackages);

        $this->assertEquals(90, $result);
    }

    private function makeParts(int $partsCount, string $gender, int $price, int $neededTime): array
    {
        $parts = [];
        for ($i = 0; $i < $partsCount; $i++) {
            $part = (new DSPartFaker($i, null, $gender, $price, $neededTime))->fakeIt();

            $parts[] = $part;
        }
        return $parts;
    }

    private function makePackages(int $packagesCount, int $partsCount, string $gender, int $packagePrice, int $partPrice, int $neededTime): array
    {
        $packages = [];
        for ($i = 0; $i < $packagesCount; $i++) {
            $package = (new DSPackageFaker($i, null, $gender, $packagePrice, (new DSPartsFaker($gender, $this->makeParts($partsCount, $gender, $partPrice, $neededTime)))->fakeIt()))->fakeIt();

            $packages[] = $package;
        }
        return $packages;
    }
}
