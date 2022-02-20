<?php

namespace Tests\Visit;

use Faker\Factory;
use Faker\Generator;
use Mockery;
use PHPUnit\Framework\TestCase;
use Tests\Fakers\Time\DSDownTimesFaker;
use TheClinic\DataStructures\Time\DSDownTime;
use TheClinic\DataStructures\Time\DSDownTimes;
use TheClinic\DataStructures\Visit\DSVisits;
use TheClinic\Exceptions\Visit\VisitSearchFailure;
use TheClinic\Visit\Utilities\DownTime;
use TheClinic\Visit\Utilities\SearchingBetweenDownTimes;
use TheClinic\Visit\Utilities\SearchingBetweenTimeRange;

class SearchingBetweenDownTimesTest extends TestCase
{
    private Generator $faker;

    protected function setUp(): void
    {
        $this->faker = Factory::create();
    }

    public function testSearch(): void
    {
        $firstTS = (new \DateTime("2021-2-17 00:00:00"))->getTimestamp();
        $lastTS = (new \DateTime("2021-2-17 12:00:00"))->getTimestamp();

        /** @var \TheClinic\Visit\Utilities\DownTime|\Mockery\MockInterface $downTime */
        $downTime = Mockery::mock(DownTime::class);

        $dsDownTimes = $this->makeDownTimes();
        $downTime->shouldReceive("findDownTimeIntruptionWithTimeRange")->andReturn($dsDownTimes);
        try {
            $timestamp = (new SearchingBetweenDownTimes(new SearchingBetweenTimeRange, $downTime))->search($firstTS, $lastTS, new DSVisits("ASC"), new DSDownTimes, 3600);
            throw new \RuntimeException("Failure!!!", 500);
        } catch (VisitSearchFailure $th) {
        }

        $firstTS = (new \DateTime("2021-2-17 00:10:00"))->getTimestamp();
        $dsDownTimes = $this->makeDownTimes();
        $downTime->shouldReceive("findDownTimeIntruptionWithTimeRange")->andReturn($dsDownTimes);
        try {
            $timestamp = (new SearchingBetweenDownTimes(new SearchingBetweenTimeRange, $downTime))->search($firstTS, $lastTS, new DSVisits("ASC"), new DSDownTimes, 3600);
            throw new \RuntimeException("Failure!!!", 500);
        } catch (VisitSearchFailure $th) {
        }
        $firstTS = (new \DateTime("2021-2-17 00:00:00"))->getTimestamp();

        $dsDownTimes = $this->makeDownTimes(11);
        $dsDownTimes[] = new DSDownTime(new \DateTime("2021-2-17 11:30:00"), new \DateTime("2021-2-17 12:00:00"));
        $downTime->shouldReceive("findDownTimeIntruptionWithTimeRange")->andReturn($dsDownTimes);
        try {
            $timestamp = (new SearchingBetweenDownTimes(new SearchingBetweenTimeRange, $downTime))->search($firstTS, $lastTS, new DSVisits("ASC"), new DSDownTimes, 3600);
            throw new \RuntimeException("Failure!!!", 500);
        } catch (VisitSearchFailure $th) {
        }

        $dsDownTimes = $this->makeDownTimes(0);
        /** @var \TheClinic\Visit\Utilities\DownTime|\Mockery\MockInterface $downTime */
        $downTime = Mockery::mock(DownTime::class);
        $downTime->shouldReceive("findDownTimeIntruptionWithTimeRange")->andReturn($dsDownTimes);
        $timestamp = (new SearchingBetweenDownTimes(new SearchingBetweenTimeRange, $downTime))->search($firstTS, $lastTS, new DSVisits("ASC"), new DSDownTimes, 3600);
        $this->assertIsInt($timestamp);
        $this->assertEquals((new \DateTime("2021-2-17 00:00:00"))->getTimestamp(), $timestamp);

        $firstTS = (new \DateTime("2021-2-17 00:10:00"))->getTimestamp();
        $dsDownTimes = $this->makeDownTimes(1);
        /** @var \TheClinic\Visit\Utilities\DownTime|\Mockery\MockInterface $downTime */
        $downTime = Mockery::mock(DownTime::class);
        $downTime->shouldReceive("findDownTimeIntruptionWithTimeRange")->andReturn($dsDownTimes);
        $timestamp = (new SearchingBetweenDownTimes(new SearchingBetweenTimeRange, $downTime))->search($firstTS, $lastTS, new DSVisits("ASC"), new DSDownTimes, 3600);
        $this->assertIsInt($timestamp);
        $this->assertEquals((new \DateTime("2021-2-17 00:30:00"))->getTimestamp(), $timestamp);
        $firstTS = (new \DateTime("2021-2-17 00:00:00"))->getTimestamp();

        $dsDownTimes = $this->makeDownTimes(6);
        /** @var \TheClinic\Visit\Utilities\DownTime|\Mockery\MockInterface $downTime */
        $downTime = Mockery::mock(DownTime::class);
        $downTime->shouldReceive("findDownTimeIntruptionWithTimeRange")->andReturn($dsDownTimes);
        $timestamp = (new SearchingBetweenDownTimes(new SearchingBetweenTimeRange, $downTime))->search($firstTS, $lastTS, new DSVisits("ASC"), new DSDownTimes, 3600);
        $this->assertIsInt($timestamp);
        $this->assertEquals((new \DateTime("2021-2-17 5:30:00"))->getTimestamp(), $timestamp);

        $dsDownTimes = $this->makeDownTimes(10);
        /** @var \TheClinic\Visit\Utilities\DownTime|\Mockery\MockInterface $downTime */
        $downTime = Mockery::mock(DownTime::class);
        $downTime->shouldReceive("findDownTimeIntruptionWithTimeRange")->andReturn($dsDownTimes);
        $timestamp = (new SearchingBetweenDownTimes(new SearchingBetweenTimeRange, $downTime))->search($firstTS, $lastTS, new DSVisits("ASC"), new DSDownTimes, 3600);
        $this->assertIsInt($timestamp);
        $this->assertEquals((new \DateTime("2021-2-17 09:30:00"))->getTimestamp(), $timestamp);

        $dsDownTimes = $this->makeDownTimes(11);
        /** @var \TheClinic\Visit\Utilities\DownTime|\Mockery\MockInterface $downTime */
        $downTime = Mockery::mock(DownTime::class);
        $downTime->shouldReceive("findDownTimeIntruptionWithTimeRange")->andReturn($dsDownTimes);
        $timestamp = (new SearchingBetweenDownTimes(new SearchingBetweenTimeRange, $downTime))->search($firstTS, $lastTS, new DSVisits("ASC"), new DSDownTimes, 3600);
        $this->assertIsInt($timestamp);
        $this->assertEquals((new \DateTime("2021-2-17 10:30:00"))->getTimestamp(), $timestamp);

        $dsDownTimes = $this->makeDownTimes(11);
        $dsDownTimes[] = new DSDownTime(new \DateTime("2021-2-17 11:30:00"), new \DateTime("2021-2-17 12:00:00"));
        /** @var \TheClinic\Visit\Utilities\DownTime|\Mockery\MockInterface $downTime */
        $downTime = Mockery::mock(DownTime::class);
        $downTime->shouldReceive("findDownTimeIntruptionWithTimeRange")->andReturn($dsDownTimes);
        $timestamp = (new SearchingBetweenDownTimes(new SearchingBetweenTimeRange, $downTime))->search($firstTS, $lastTS, new DSVisits("ASC"), new DSDownTimes, 3600);
        $this->assertIsInt($timestamp);
        $this->assertEquals((new \DateTime("2021-2-17 10:30:00"))->getTimestamp(), $timestamp);

        $dsDownTimes = $this->makeDownTimes(11);
        $dsDownTimes[] = new DSDownTime(new \DateTime("2021-2-17 11:40:00"), new \DateTime("2021-2-17 12:10:00"));
        /** @var \TheClinic\Visit\Utilities\DownTime|\Mockery\MockInterface $downTime */
        $downTime = Mockery::mock(DownTime::class);
        $downTime->shouldReceive("findDownTimeIntruptionWithTimeRange")->andReturn($dsDownTimes);
        $timestamp = (new SearchingBetweenDownTimes(new SearchingBetweenTimeRange, $downTime))->search($firstTS, $lastTS, new DSVisits("ASC"), new DSDownTimes, 3600);
        $this->assertIsInt($timestamp);
        $this->assertEquals((new \DateTime("2021-2-17 10:30:00"))->getTimestamp(), $timestamp);
    }

    private function makeDownTimes(int|null $exception = null): DSDownTimes
    {
        for ($i = 0; $i < 12; $i++) {
            if (!is_null($exception) && $i === $exception) {
                continue;
            }

            $customData[] = [new \DateTime("2021-2-17" . strval($i) . ":00:00"), new \DateTime("2021-2-17" . strval($i) . ":30:00")];
        }

        return (new DSDownTimesFaker($customData))->fakeIt();
    }
}
