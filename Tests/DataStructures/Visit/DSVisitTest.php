<?php

namespace TheClinic\Tests\DataStructures\Visit;

use Faker\Factory;
use Faker\Generator;
use Mockery;
use PHPUnit\Framework\TestCase;
use TheClinic\DataStructures\Order\DSOrder;
use TheClinic\DataStructures\User\DSUser;
use TheClinic\DataStructures\Visit\DSVisit;

class DSVisitTest extends TestCase
{
    private Generator $faker;

    protected function setUp(): void
    {
        $this->faker = Factory::create();
    }

    public function testDataStructure(): void
    {
        $id = $this->faker->numberBetween(1, 1000);

        /** @var \TheClinic\DataStructures\User\DSUser|\Mockery\MockInterface $user */
        $user = Mockery::mock(DSUser::class);
        $user->shouldReceive("getId")->andReturn($this->faker->numberBetween(1, 1000));

        /** @var \TheClinic\DataStructures\Order\DSOrder|\Mockery\MockInterface $order */
        $order = Mockery::mock(DSOrder::class);
        $order->shouldReceive("getId")->andReturn($this->faker->numberBetween(1, 1000));

        $visitTimestamp = (new \DateTime())->modify("+1 week")->getTimestamp();

        $consumingTime = $this->faker->numberBetween(600, 3600);

        $createdAt = new \DateTime();
        $updatedAt = new \DateTime();

        $dsVisit = new DSVisit(
            $id,
            $user,
            $order,
            $visitTimestamp,
            $consumingTime,
            null,
            null,
            $createdAt,
            $updatedAt,
        );

        $this->assertEquals($dsVisit->getId(), $id);        
        $this->assertEquals($dsVisit->user->getId(), $user->getId());
        $this->assertEquals($dsVisit->order->getId(), $order->getId());

        $this->assertEquals($dsVisit->getVisitTimestamp(), $visitTimestamp);
        $this->assertEquals($dsVisit->getConsumingTime(), $consumingTime);

        $this->assertEquals($dsVisit->getCreatedAt()->getTimestamp(), $createdAt->getTimestamp());
        $this->assertEquals($dsVisit->getUpdatedAt()->getTimestamp(), $updatedAt->getTimestamp());
    }
}
