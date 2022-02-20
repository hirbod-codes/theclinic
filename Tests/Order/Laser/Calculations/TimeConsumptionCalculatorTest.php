<?php

namespace Tests\Order\Laser\Calculations;

use Faker\Factory;
use Faker\Generator;
use PHPUnit\Framework\TestCase;
use Tests\Fakers\Order\DSPackageFaker;
use Tests\Fakers\Order\DSPackagesFaker;
use Tests\Fakers\Order\DSPartFaker;
use Tests\Fakers\Order\DSPartsFaker;
use TheClinic\Order\Laser\Calculations\TimeConsumptionCalculator;

class TimeConsumptionCalculatorTest extends TestCase
{
    private Generator $faker;

    protected function setUp(): void
    {
        $this->faker = Factory::create();

        $gender = "Male";

        $this->dsParts = (new DSPartsFaker($gender, $this->makeParts(3, $gender, 10, 600)))->fakeIt();

        $this->dsPackages = (new DSPackagesFaker($gender, $this->makePackages(2, 3, $gender, 20, 10, 600)))->fakeIt();
    }

    public function testCalculate(): void
    {
        $result = (new TimeConsumptionCalculator)->calculate($this->dsParts, $this->dsPackages);

        $this->assertEquals(5400, $result);
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
