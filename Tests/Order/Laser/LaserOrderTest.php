<?php

namespace Tests\Order\Laser;

use Faker\Factory;
use Faker\Generator;
use Mockery;
use Tests\TestCase;
use TheClinicDataStructures\DataStructures\Order\DSPackages;
use TheClinicDataStructures\DataStructures\Order\DSParts;
use TheClinic\Exceptions\Order\InvalidGenderException;
use TheClinic\Exceptions\Order\NoPackageOrPartException;
use TheClinic\Order\Laser\ILaserPriceCalculator;
use TheClinic\Order\Laser\ILaserTimeConsumptionCalculator;
use TheClinic\Order\Laser\LaserOrder;

class LaserOrderTest extends TestCase
{
    private Generator $faker;

    private DSParts $parts;

    private DSPackages $packages;

    private ILaserPriceCalculator $laserPriceCalculator;

    private ILaserTimeConsumptionCalculator $laserTimeConsumptionCalculator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->faker = Factory::create();

        $gender = "Male";

        /** @var \TheClinicDataStructures\DataStructures\Order\DSParts|\Mockery\MockInterface $parts */
        $this->parts = Mockery::mock(DSParts::class);
        $this->parts->shouldReceive("getGender")->andReturn($gender);

        /** @var \TheClinicDataStructures\DataStructures\Order\DSPackages|\Mockery\MockInterface $packages */
        $this->packages = Mockery::mock(DSPackages::class);
        $this->packages->shouldReceive("getGender")->andReturn($gender);

        /** @var \TheClinic\Order\Laser\ILaserPriceCalculator|\Mockery\MockInterface $laserPriceCalculator */
        $this->laserPriceCalculator = Mockery::mock(ILaserPriceCalculator::class);
        $this->laserPriceCalculator->shouldReceive("calculate")->with($this->parts, $this->packages)->andReturn(1);
        $this->laserPriceCalculator->shouldReceive("calculateWithoutDiscount")->with($this->parts, $this->packages)->andReturn(1);

        /** @var \TheClinic\Order\Laser\ILaserTimeConsumptionCalculator|\Mockery\MockInterface $laserTimeConsumptionCalculator */
        $this->laserTimeConsumptionCalculator = Mockery::mock(ILaserTimeConsumptionCalculator::class);
        $this->laserTimeConsumptionCalculator->shouldReceive("calculate")->with($this->parts, $this->packages)->andReturn(1);
    }

    public function testLaserOrderCalculatePrice(): void
    {
        $laserOrder = new LaserOrder;
        $result = $laserOrder->calculatePrice($this->parts, $this->packages, $this->laserPriceCalculator);

        $this->assertIsInt($result);

        try {
            (new LaserOrder)->calculatePrice(null, null, $this->laserPriceCalculator);
            throw new \RuntimeException("Failure!!!", 500);
        } catch (NoPackageOrPartException $th) {
        }

        /** @var \TheClinic\DataStructures\Order\DSParts|\Mockery\MockInterface $parts */
        $this->parts = Mockery::mock(DSParts::class);
        $this->parts->shouldReceive("getGender")->andReturn("Female");

        try {
            (new LaserOrder)->calculatePrice($this->parts, $this->packages, $this->laserPriceCalculator);
            throw new \RuntimeException("Failure!!!", 500);
        } catch (InvalidGenderException $th) {
        }
    }

    public function testLaserOrderCalculatePriceWithoutDiscount(): void
    {
        $laserOrder = new LaserOrder;
        $result = $laserOrder->calculatePriceWithoutDiscount($this->parts, $this->packages, $this->laserPriceCalculator);

        $this->assertIsInt($result);
    }

    public function testLaserOrderCalculateTimeConsumption(): void
    {
        $laserOrder = new LaserOrder;
        $result = $laserOrder->calculateTimeConsumption($this->parts, $this->packages, $this->laserTimeConsumptionCalculator);

        $this->assertIsInt($result);
    }
}
