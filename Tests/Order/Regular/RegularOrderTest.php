<?php

namespace Tests\Order\Laser;

use Faker\Factory;
use Faker\Generator;
use Tests\TestCase;
use TheClinic\Order\Regular\RegularOrder;

class RegularOrderTest extends TestCase
{
    private Generator $faker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->faker = Factory::create();
    }

    public function testCalculatePrice(): void
    {
        $result = (new RegularOrder)->calculatePrice();
        $this->assertEquals(400000, $result);
    }

    public function testCalculateTimeConsumption(): void
    {
        $result = (new RegularOrder)->calculateTimeConsumption();
        $this->assertEquals(600, $result);
    }
}
