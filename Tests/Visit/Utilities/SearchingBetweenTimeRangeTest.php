<?php

namespace Tests\Visit;

use Faker\Factory;
use Faker\Generator;
use PHPUnit\Framework\TestCase;
use TheClinic\DataStructures\Visit\DSVisit;
use TheClinic\DataStructures\Visit\DSVisits;
use TheClinic\Visit\Utilities\SearchingBetweenTimeRange;

class SearchingBetweenTimeRangeTest extends TestCase
{
    private Generator $faker;

    protected function setUp(): void
    {
        $this->faker = Factory::create();
    }

    public function testSearch(): void
    {
        $timestamp = (new SearchingBetweenTimeRange)->search((new \DateTime("00:00:00"))->getTimestamp(), (new \DateTime("12:00:00"))->getTimestamp(), 1800, new DSVisits("ASC"));
        $this->assertIsInt($timestamp);
        $this->assertEquals((new \DateTime("00:00:00"))->getTimestamp(), (new \DateTime())->setTimestamp($timestamp)->getTimestamp());

        $dsVisits = $this->makeFutureVisits(0);

        $timestamp = (new SearchingBetweenTimeRange)->search((new \DateTime("00:00:00"))->getTimestamp(), (new \DateTime("12:00:00"))->getTimestamp(), 1800, $dsVisits);
        $this->assertIsInt($timestamp);
        $this->assertEquals((new \DateTime("00:00:00"))->getTimestamp(), (new \DateTime())->setTimestamp($timestamp)->getTimestamp());

        $dsVisits = $this->makeFutureVisits(6);

        $timestamp = (new SearchingBetweenTimeRange)->search((new \DateTime("00:10:00"))->getTimestamp(), (new \DateTime("12:00:00"))->getTimestamp(), 1800, $dsVisits);
        $this->assertIsInt($timestamp);
        $this->assertEquals((new \DateTime("00:30:00"))->getTimestamp(), (new \DateTime())->setTimestamp($timestamp)->getTimestamp());

        $timestamp = (new SearchingBetweenTimeRange)->search((new \DateTime("00:00:00"))->getTimestamp(), (new \DateTime("12:00:00"))->getTimestamp(), 1800, $dsVisits);
        $this->assertIsInt($timestamp);
        $this->assertEquals((new \DateTime("00:30:00"))->getTimestamp(), (new \DateTime())->setTimestamp($timestamp)->getTimestamp());

        $timestamp = (new SearchingBetweenTimeRange)->search((new \DateTime("00:00:00"))->getTimestamp(), (new \DateTime("12:00:00"))->getTimestamp(), 3600, $dsVisits);
        $this->assertIsInt($timestamp);
        $this->assertEquals((new \DateTime("05:30:00"))->getTimestamp(), (new \DateTime())->setTimestamp($timestamp)->getTimestamp());

        $dsVisits = $this->makeFutureVisits(11);

        $timestamp = (new SearchingBetweenTimeRange)->search((new \DateTime("00:00:00"))->getTimestamp(), (new \DateTime("12:00:00"))->getTimestamp(), 3600, $dsVisits);
        $this->assertIsInt($timestamp);
        $this->assertEquals((new \DateTime("10:30:00"))->getTimestamp(), (new \DateTime())->setTimestamp($timestamp)->getTimestamp());

        $visit = new \DateTime("11:40:00");
        $dsVisits[] = new DSVisit(45, null, null, $visit->getTimestamp(), 1800, null, null, new \DateTime(), new \DateTime());

        $timestamp = (new SearchingBetweenTimeRange)->search((new \DateTime("00:00:00"))->getTimestamp(), (new \DateTime("12:00:00"))->getTimestamp(), 3600, $dsVisits);
        $this->assertIsInt($timestamp);
        $this->assertEquals((new \DateTime("10:30:00"))->getTimestamp(), (new \DateTime())->setTimestamp($timestamp)->getTimestamp());

        $dsVisits = $this->makeFutureVisits(11);
        $visit = new \DateTime("11:30:00");
        $dsVisits[] = new DSVisit(45, null, null, $visit->getTimestamp(), 1800, null, null, new \DateTime(), new \DateTime());

        $timestamp = (new SearchingBetweenTimeRange)->search((new \DateTime("00:00:00"))->getTimestamp(), (new \DateTime("12:00:00"))->getTimestamp(), 3600, $dsVisits);
        $this->assertIsInt($timestamp);
        $this->assertEquals((new \DateTime("10:30:00"))->getTimestamp(), (new \DateTime())->setTimestamp($timestamp)->getTimestamp());
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
            $dsVisits[] = new DSVisit($i, null, null, $visit->getTimestamp(), $consumingTime, null, null, new \DateTime(), new \DateTime());
        }
        return $dsVisits;
    }
}
