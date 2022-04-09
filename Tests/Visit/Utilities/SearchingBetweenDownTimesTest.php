<?php

namespace Tests\Visit;

use Faker\Factory;
use Faker\Generator;
use Mockery;
use Tests\TestCase;
use Tests\Fakers\Time\DSDownTimesFaker;
use TheClinic\Exceptions\Visit\NeededTimeOutOfRange;
use TheClinicDataStructures\DataStructures\Time\DSDownTime;
use TheClinicDataStructures\DataStructures\Time\DSDownTimes;
use TheClinicDataStructures\DataStructures\Visit\DSVisits;
use TheClinic\Exceptions\Visit\VisitSearchFailure;
use TheClinic\Visit\Utilities\DownTime;
use TheClinic\Visit\Utilities\SearchingBetweenDownTimes;
use TheClinic\Visit\Utilities\SearchingBetweenTimeRange;

class SearchingBetweenDownTimesTest extends TestCase
{
    private Generator $faker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->faker = Factory::create();
    }

    public function testSearch(): void
    {
        $firstTS = (new \DateTime("2021-2-17 00:00:00"))->getTimestamp();
        $lastTS = (new \DateTime("2021-2-17 12:00:00"))->getTimestamp();

        $firstTS = (new \DateTime("2021-2-17 00:00:00"))->getTimestamp();
        $this->testsuccessfulSearch($firstTS, $lastTS, 3600, (new \DateTime("2021-2-17 10:30:00"))->getTimestamp(), 11);
        $firstTS = (new \DateTime("2021-2-17 00:00:00"))->getTimestamp();

        $firstTS = (new \DateTime("2021-2-17 00:00:00"))->getTimestamp();
        $lastTS = (new \DateTime("2021-2-17 10:30:00"))->getTimestamp();
        $this->testsuccessfulSearch($firstTS, $lastTS, 3600, (new \DateTime("2021-2-17 09:30:00"))->getTimestamp(), 10);
        $firstTS = (new \DateTime("2021-2-17 00:00:00"))->getTimestamp();
        $lastTS = (new \DateTime("2021-2-17 12:00:00"))->getTimestamp();

        $firstTS = (new \DateTime("2021-2-17 00:00:00"))->getTimestamp();
        $this->testsuccessfulSearch($firstTS, $lastTS, 3600, (new \DateTime("2021-2-17 00:30:00"))->getTimestamp(), 1);
        $firstTS = (new \DateTime("2021-2-17 00:00:00"))->getTimestamp();

        $firstTS = (new \DateTime("2021-2-17 00:00:00"))->getTimestamp();
        $this->testsuccessfulSearch($firstTS, $lastTS, 3600, (new \DateTime("2021-2-17 04:30:00"))->getTimestamp(), 5);
        $firstTS = (new \DateTime("2021-2-17 00:00:00"))->getTimestamp();

        $firstTS = (new \DateTime("2021-2-17 00:10:00"))->getTimestamp();
        $this->testsuccessfulSearch($firstTS, $lastTS, 3600, (new \DateTime("2021-2-17 00:30:00"))->getTimestamp(), 1);
        $firstTS = (new \DateTime("2021-2-17 00:00:00"))->getTimestamp();

        $firstTS = (new \DateTime("2021-2-17 00:30:00"))->getTimestamp();
        $this->testsuccessfulSearch($firstTS, $lastTS, 3600, (new \DateTime("2021-2-17 00:30:00"))->getTimestamp(), 1);
        $firstTS = (new \DateTime("2021-2-17 00:00:00"))->getTimestamp();

        $firstTS = (new \DateTime("2021-2-17 00:40:00"))->getTimestamp();
        $this->testsuccessfulSearch($firstTS, $lastTS, 3600, (new \DateTime("2021-2-17 00:40:00"))->getTimestamp(), 1);
        $firstTS = (new \DateTime("2021-2-17 00:00:00"))->getTimestamp();

        $firstTS = (new \DateTime("2021-2-17 00:30:00"))->getTimestamp();
        $lastTS = (new \DateTime("2021-2-17 00:50:00"))->getTimestamp();
        $this->testsuccessfulSearch($firstTS, $lastTS, 1200, (new \DateTime("2021-2-17 00:30:00"))->getTimestamp());
        $firstTS = (new \DateTime("2021-2-17 00:00:00"))->getTimestamp();
        $lastTS = (new \DateTime("2021-2-17 12:00:00"))->getTimestamp();

        $firstTS = (new \DateTime("2021-2-17 00:10:00"))->getTimestamp();
        $lastTS = (new \DateTime("2021-2-17 00:30:00"))->getTimestamp();
        $this->testFailingSearch($firstTS, $lastTS, 1200);
        $firstTS = (new \DateTime("2021-2-17 00:00:00"))->getTimestamp();
        $lastTS = (new \DateTime("2021-2-17 12:00:00"))->getTimestamp();
    }

    private function makeDownTimes(int|null $exception = null): DSDownTimes
    {
        for ($i = 0; $i < 12; $i++) {
            if (!is_null($exception) && $i === $exception) {
                continue;
            }

            $customData[] = [new \DateTime("2021-2-17" . strval($i) . ":00:00"), new \DateTime("2021-2-17" . strval($i) . ":30:00"), $this->faker->lexify()];
        }

        return (new DSDownTimesFaker($customData))->fakeIt();
    }

    private function testsuccessfulSearch(int $firstTS, int $lastTS, int $consumingTime, int $expectedTimestamp, int|null $exception = null): void
    {
        $dsDownTimes = $this->makeDownTimes($exception);
        /** @var \TheClinic\Visit\Utilities\DownTime|\Mockery\MockInterface $downTime */
        $downTime = Mockery::mock(DownTime::class);
        $downTime->shouldReceive("findDownTimeIntruptionWithTimeRange")->andReturn($dsDownTimes);
        $timestamp = (new SearchingBetweenDownTimes(null, null, $downTime))->search($firstTS, $lastTS, new DSVisits("ASC"), $dsDownTimes, $consumingTime);
        $this->assertIsInt($timestamp);
        $this->assertEquals($expectedTimestamp, $timestamp);
    }

    private function testFailingSearch(int $firstTS, int $lastTS, int $consumingTime, int|null $exception = null): void
    {
        $dsDownTimes = $this->makeDownTimes($exception);
        /** @var \TheClinic\Visit\Utilities\DownTime|\Mockery\MockInterface $downTime */
        $downTime = Mockery::mock(DownTime::class);
        $downTime->shouldReceive("findDownTimeIntruptionWithTimeRange")->andReturn($dsDownTimes);
        try {
            $timestamp = (new SearchingBetweenDownTimes(null, null, $downTime))->search(
                $firstTS,
                $lastTS,
                new DSVisits("ASC"),
                new DSDownTimes,
                $consumingTime
            );
            throw new \RuntimeException("Failure!!!", 500);
        } catch (VisitSearchFailure $th) {
        }
    }
}
