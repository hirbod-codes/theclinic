<?php

namespace Tests\Order\Laser;

use Faker\Factory;
use Faker\Generator;
use Mockery;
use PHPUnit\Framework\TestCase;
use TheClinic\DataStructures\Order\DSPackages;
use TheClinic\DataStructures\Order\DSParts;
use TheClinic\Exceptions\Order\InvalidGenderException;
use TheClinic\Exceptions\Order\NoPackageOrPartException;
use TheClinic\Order\Laser\ILaserPriceCalculator;
use TheClinic\Order\Laser\ILaserTimeConsumptionCalculator;
use TheClinic\Order\Laser\LaserOrder;

class LaserOrderTest extends TestCase
{
    private Generator $faker;

    private DSParts|Mockery\MockInterface $parts;

    private DSPackages|Mockery\MockInterface $packages;

    private ILaserPriceCalculator|Mockery\MockInterface $laserPriceCalculator;

    private ILaserTimeConsumptionCalculator|Mockery\MockInterface $laserTimeConsumptionCalculator;

    protected function setUp(): void
    {
        $this->faker = Factory::create();

        $gender = "Male";

        $this->parts = Mockery::mock(DSParts::class);
        $this->parts->shouldReceive("getGender")->andReturn($gender);

        $this->packages = Mockery::mock(DSPackages::class);
        $this->packages->shouldReceive("getGender")->andReturn($gender);

        $this->laserPriceCalculator = Mockery::mock(ILaserPriceCalculator::class);
        $this->laserPriceCalculator->shouldReceive("calculate")->with($this->parts, $this->packages)->andReturn(1);
        $this->laserPriceCalculator->shouldReceive("calculateWithoutDiscount")->with($this->parts, $this->packages)->andReturn(1);

        $this->laserTimeConsumptionCalculator = Mockery::mock(ILaserTimeConsumptionCalculator::class);
        $this->laserTimeConsumptionCalculator->shouldReceive("calculate")->with($this->parts, $this->packages)->andReturn(1);
    }

    public function testLaserOrder(): void
    {
        $laserOrder = new LaserOrder($this->parts, $this->packages, $this->laserPriceCalculator, $this->laserTimeConsumptionCalculator);
        $this->assertInstanceOf(LaserOrder::class, $laserOrder);

        $laserOrder = new LaserOrder(null, $this->packages, $this->laserPriceCalculator, $this->laserTimeConsumptionCalculator);
        $this->assertInstanceOf(LaserOrder::class, $laserOrder);

        try {
            new LaserOrder(null, null, $this->laserPriceCalculator, $this->laserTimeConsumptionCalculator);

            throw new \RuntimeException("Failure!!!", 500);
        } catch (NoPackageOrPartException $th) {
        }

        $this->parts = Mockery::mock(DSParts::class);
        $this->parts->shouldReceive("getGender")->andReturn("Female");

        try {
            new LaserOrder($this->parts, $this->packages, $this->laserPriceCalculator, $this->laserTimeConsumptionCalculator);

            throw new \RuntimeException("Failure!!!", 500);
        } catch (InvalidGenderException $th) {
        }
    }

    public function testLaserOrderCalculatePrice(): void
    {
        $laserOrder = new LaserOrder($this->parts, $this->packages, $this->laserPriceCalculator, $this->laserTimeConsumptionCalculator);
        $result = $laserOrder->calculatePrice();

        $this->assertIsInt($result);
    }

    public function testLaserOrderCalculatePriceWithoutDiscount(): void
    {
        $laserOrder = new LaserOrder($this->parts, $this->packages, $this->laserPriceCalculator, $this->laserTimeConsumptionCalculator);
        $result = $laserOrder->calculatePriceWithoutDiscount();

        $this->assertIsInt($result);
    }

    public function testLaserOrderCalculateTimeConsumption(): void
    {
        $laserOrder = new LaserOrder($this->parts, $this->packages, $this->laserPriceCalculator, $this->laserTimeConsumptionCalculator);
        $result = $laserOrder->calculateTimeConsumption();

        $this->assertIsInt($result);
    }
}
