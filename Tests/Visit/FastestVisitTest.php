<?php

namespace Tests\Visit;

use Faker\Factory;
use Faker\Generator;
use Mockery;
use Tests\TestCase;
use Tests\Fakers\Time\DSDownTimesFaker;
use Tests\Fakers\Time\DSWorkScheduleFaker;
use TheClinicDataStructure\DataStructures\Time\DSDownTimes;
use TheClinicDataStructure\DataStructures\Time\DSWorkSchedule;
use TheClinicDataStructure\DataStructures\Visit\DSVisits;
use TheClinic\Visit\FastestVisit;
use TheClinic\Visit\Utilities\DownTime;
use TheClinic\Visit\Utilities\SearchingBetweenDownTimes;
use TheClinic\Visit\Utilities\SearchingBetweenTimeRange;
use TheClinic\Visit\Utilities\WorkSchedule;

class FastestVisitTest extends TestCase
{
    private Generator $faker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->faker = Factory::create();
    }

    public function testFindVisit(): void
    {
        $futureDays = 1;

        $now = new \DateTime();
        $testStartingTime = (new \DateTime($now->format("Y-m-d") . " 00:00:00"))->modify("+1 day");
        $dsDownTimes = $this->makeDSDowntimes($testStartingTime, $futureDays);

        $timestamp = (new FastestVisit(
            $testStartingTime,
            3600,
            $this->makeFutureVisits(),
            $this->makeDSWorkSchedule(),
            $dsDownTimes,
            new SearchingBetweenDownTimes(new SearchingBetweenTimeRange, new DownTime),
            new WorkSchedule,
            new DownTime
        ))->findVisit();
        $this->assertIsInt($timestamp);
        $this->assertEquals(
            ($expected = new \DateTime())->setTimestamp($testStartingTime->getTimestamp())->modify("+" . strval($futureDays) . " days")->modify("+8 hours")->getTimestamp(),
            $timestamp,
            "now : " . (new \DateTime)->format("Y-m-d H:i:s l") . " expected is: " . $expected->format("Y-m-d H:i:s l") .
                " and actual is: " . (new \DateTime)->setTimestamp($timestamp)->format("Y-m-d H:i:s l")
        );

        // A gap at start
        $now = new \DateTime();
        $testStartingTime = (new \DateTime($now->format("Y-m-d") . " 00:00:00"))->modify("+1 day");
        $dsDownTimes = $this->makeDSDowntimes($testStartingTime, $futureDays);
        unset($dsDownTimes[8]);

        $timestamp = (new FastestVisit(
            $testStartingTime,
            3600,
            $this->makeFutureVisits(),
            $this->makeDSWorkSchedule(),
            $dsDownTimes,
            new SearchingBetweenDownTimes(new SearchingBetweenTimeRange, new DownTime),
            new WorkSchedule,
            new DownTime
        ))->findVisit();
        $this->assertIsInt($timestamp);
        $this->assertEquals(
            ($expected = new \DateTime())->setTimestamp($testStartingTime->getTimestamp())->modify("+8 hours")->getTimestamp(),
            $timestamp
        );

        // No gap at start
        $now = new \DateTime();
        $testStartingTime = (new \DateTime($now->format("Y-m-d") . " 00:00:00"))->modify("+1 day");
        $dsDownTimes = $this->makeDSDowntimes($testStartingTime, $futureDays);
        unset($dsDownTimes[9]);
        $testStartingTime->modify("+8 hours")->modify("+10 minutes");

        $timestamp = (new FastestVisit(
            $testStartingTime,
            3600,
            $this->makeFutureVisits(),
            $this->makeDSWorkSchedule(),
            $dsDownTimes,
            new SearchingBetweenDownTimes(new SearchingBetweenTimeRange, new DownTime),
            new WorkSchedule,
            new DownTime
        ))->findVisit();
        $this->assertIsInt($timestamp);
        $this->assertEquals(
            ($expected = new \DateTime())->setTimestamp($testStartingTime->getTimestamp())->modify("+20 minutes")->getTimestamp(),
            $timestamp,
            "now : " . (new \DateTime)->format("Y-m-d H:i:s l") . " expected is: " . $expected->format("Y-m-d H:i:s l") .
                " and actual is: " . (new \DateTime)->setTimestamp($timestamp)->format("Y-m-d H:i:s l")
        );

        // A random gap 
        $now = new \DateTime();
        $testStartingTime = (new \DateTime($now->format("Y-m-d") . " 00:00:00"))->modify("+1 day");
        $dsDownTimes = $this->makeDSDowntimes($testStartingTime, $futureDays);
        unset($dsDownTimes[9]);
        $testStartingTime->modify("+8 hours")->modify("+40 minutes");

        $timestamp = (new FastestVisit(
            $testStartingTime,
            3600,
            $this->makeFutureVisits(),
            $this->makeDSWorkSchedule(),
            $dsDownTimes,
            new SearchingBetweenDownTimes(new SearchingBetweenTimeRange, new DownTime),
            new WorkSchedule,
            new DownTime
        ))->findVisit();
        $this->assertIsInt($timestamp);
        $this->assertEquals(
            $testStartingTime->getTimestamp(),
            $timestamp
        );

        // A random gap 
        $now = new \DateTime();
        $testStartingTime = (new \DateTime($now->format("Y-m-d") . " 00:00:00"))->modify("+1 day");
        $dsDownTimes = $this->makeDSDowntimes($testStartingTime, $futureDays);
        unset($dsDownTimes[10]);

        $timestamp = (new FastestVisit(
            $testStartingTime,
            3600,
            $this->makeFutureVisits(),
            $this->makeDSWorkSchedule(),
            $dsDownTimes,
            new SearchingBetweenDownTimes(new SearchingBetweenTimeRange, new DownTime),
            new WorkSchedule,
            new DownTime
        ))->findVisit();
        $this->assertIsInt($timestamp);
        $this->assertEquals(
            (new \DateTime())->setTimestamp($testStartingTime->getTimestamp())->modify("+9 hours")->modify("+30 minutes")->getTimestamp(),
            $timestamp
        );
    }

    private function makeFutureVisits(): DSVisits
    {
        return new DSVisits("ASC");
    }

    private function makeDSWorkSchedule(): DSWorkSchedule
    {
        return (new DSWorkScheduleFaker())->fakeIt();
    }

    /**
     * @param integer $startFrom 0 means down times will start from now, 1 means they'll start from next first hour, -1 means they start from the hour before.
     * @return \TheClinicDataStructure\DataStructures\Time\DSDownTimes
     */
    private function makeDSDowntimes(\DateTime $now, int $days): DSDownTimes
    {
        $pointer = (new \DateTime())->setTimestamp($now->getTimestamp());
        $limit = (new \DateTime())->setTimestamp($now->getTimestamp())->modify("+" . strval($days) . " days")->getTimestamp();

        $first = true;
        $customData = [];
        $start = (new \DateTime())->setTimestamp($pointer->getTimestamp());

        while ((new \DateTime())->setTimestamp($pointer->getTimestamp())->modify("+90 minutes")->getTimestamp() <= $limit) {
            if (!$first) {
                $start = (new \DateTime())->setTimestamp($pointer->modify("+1 hour")->getTimestamp());
            }
            $end = (new \DateTime())->setTimestamp($start->getTimestamp())->modify("+30 minutes");

            $customData[] = [$start, $end];

            $first = false;
        }

        return (new DSDownTimesFaker($customData))->fakeIt();
    }
}
