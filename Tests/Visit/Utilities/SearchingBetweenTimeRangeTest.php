<?php

namespace Tests\Visit;

use Faker\Factory;
use Faker\Generator;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;
use TheClinicDataStructures\DataStructures\Visit\DSVisit;
use TheClinicDataStructures\DataStructures\Visit\DSVisits;
use TheClinic\Visit\Utilities\SearchingBetweenTimeRange;
use TheClinicDataStructures\DataStructures\Order\DSOrder;
use TheClinicDataStructures\DataStructures\User\DSUser;
use TheClinicDataStructures\DataStructures\Visit\Laser\DSLaserVisit;

class SearchingBetweenTimeRangeTest extends TestCase
{
    private Generator $faker;

    private DSUser|MockInterface $user;

    private DSOrder|MockInterface $order;

    protected function setUp(): void
    {
        parent::setUp();

        $this->faker = Factory::create();

        /** @var DSUser|MockInterface $user */
        $this->user = Mockery::mock(DSUser::class);

        /** @var DSOrder|MockInterface $order */
        $this->order = Mockery::mock(DSOrder::class);
    }

    public function testSearch(): void
    {
        $timestamp = (new SearchingBetweenTimeRange)->search((new \DateTime("00:00:00"))->getTimestamp(), (new \DateTime("12:00:00"))->getTimestamp(), 1800, new DSVisits("ASC"));
        $this->assertIsInt($timestamp);
        $this->assertEquals((new \DateTime("00:00:00"))->getTimestamp(), $timestamp);

        $dsVisits = $this->makeFutureVisits(0);

        $timestamp = (new SearchingBetweenTimeRange)->search((new \DateTime("00:00:00"))->getTimestamp(), (new \DateTime("12:00:00"))->getTimestamp(), 1800, $dsVisits);
        $this->assertIsInt($timestamp);
        $this->assertEquals((new \DateTime("00:00:00"))->getTimestamp(), $timestamp);

        $dsVisits = $this->makeFutureVisits(6);

        $timestamp = (new SearchingBetweenTimeRange)->search((new \DateTime("00:10:00"))->getTimestamp(), (new \DateTime("12:00:00"))->getTimestamp(), 1800, $dsVisits);
        $this->assertIsInt($timestamp);
        $this->assertEquals((new \DateTime("00:30:00"))->getTimestamp(), $timestamp);

        $timestamp = (new SearchingBetweenTimeRange)->search((new \DateTime("00:30:00"))->getTimestamp(), (new \DateTime("12:00:00"))->getTimestamp(), 1800, $dsVisits);
        $this->assertIsInt($timestamp);
        $this->assertEquals((new \DateTime("00:30:00"))->getTimestamp(), $timestamp);

        $timestamp = (new SearchingBetweenTimeRange)->search((new \DateTime("00:40:00"))->getTimestamp(), (new \DateTime("12:00:00"))->getTimestamp(), 1200, $dsVisits);
        $this->assertIsInt($timestamp);
        $this->assertEquals((new \DateTime("00:40:00"))->getTimestamp(), $timestamp);

        $timestamp = (new SearchingBetweenTimeRange)->search((new \DateTime("00:00:00"))->getTimestamp(), (new \DateTime("12:00:00"))->getTimestamp(), 1800, $dsVisits);
        $this->assertIsInt($timestamp);
        $this->assertEquals((new \DateTime("00:30:00"))->getTimestamp(), $timestamp);

        $timestamp = (new SearchingBetweenTimeRange)->search((new \DateTime("00:00:00"))->getTimestamp(), (new \DateTime("12:00:00"))->getTimestamp(), 3600, $dsVisits);
        $this->assertIsInt($timestamp);
        $this->assertEquals((new \DateTime("05:30:00"))->getTimestamp(), $timestamp);

        $dsVisits = $this->makeFutureVisits(11);

        $timestamp = (new SearchingBetweenTimeRange)->search((new \DateTime("00:00:00"))->getTimestamp(), (new \DateTime("12:00:00"))->getTimestamp(), 3600, $dsVisits);
        $this->assertIsInt($timestamp);
        $this->assertEquals((new \DateTime("10:30:00"))->getTimestamp(), $timestamp);

        $dsVisits = $this->makeFutureVisits(10);

        $timestamp = (new SearchingBetweenTimeRange)->search((new \DateTime("00:00:00"))->getTimestamp(), (new \DateTime("10:30:00"))->getTimestamp(), 3600, $dsVisits);
        $this->assertIsInt($timestamp);
        $this->assertEquals((new \DateTime("09:30:00"))->getTimestamp(), $timestamp);

        $timestamp = (new SearchingBetweenTimeRange)->search((new \DateTime("00:00:00"))->getTimestamp(), (new \DateTime("11:10:00"))->getTimestamp(), 3600, $dsVisits);
        $this->assertIsInt($timestamp);
        $this->assertEquals((new \DateTime("09:30:00"))->getTimestamp(), $timestamp);
    }

    private function makeFutureVisits(int|null $exception = null): DSVisits
    {
        $dsVisits = new DSVisits("ASC");
        $consumingTime = 1800;

        for ($i = 0; $i < 12; $i++) {
            if ($exception !== null && $i === $exception) {
                continue;
            }

            $visit = new \DateTime(strval($i) . ":00:00");
            $dsVisits[] = new DSLaserVisit($i, $visit->getTimestamp(), $consumingTime, new \DateTime(), new \DateTime());
        }
        return $dsVisits;
    }
}
