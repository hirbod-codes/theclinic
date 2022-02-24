<?php

namespace Tests\DataStructures\Time;

use Faker\Factory;
use Faker\Generator;
use Mockery;
use Tests\TestCase;
use TheClinic\DataStructures\Time\DSDownTime;
use TheClinic\DataStructures\Time\DSDownTimes;

class DSDownTimesTest extends TestCase
{
    private Generator $faker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->faker = Factory::create();
    }

    public function testDataStructure(): void
    {
        $this->testArrayAccess();
        $this->testIterator();
    }

    private function testArrayAccess(): void
    {
        $downTimes = $this->makeDownTimes(10);

        $dsDownTimes = new DSDownTimes();

        foreach ($downTimes as $dsDownTime) {
            $dsDownTimes[] = $dsDownTime;
        }

        $this->assertCount(count($downTimes), $dsDownTimes);

        $dsDownTimes = new DSDownTimes;

        for ($i = 0; $i < count($downTimes); $i++) {
            $dsDownTimes[$i] = $downTimes[$i];
        }

        $this->assertCount(count($downTimes), $dsDownTimes);

        unset($dsDownTimes[1]);
        unset($dsDownTimes[3]);

        $dsDownTimes[2] = $this->makeCustomDownTimes("5:00:00", "5:30:00");

        try {
            $dsDownTimes[5] = $this->makeCustomDownTimes("11:50:00", "12:20:00");
        } catch (\Throwable $th) {
        }
    }

    private function testIterator(): void
    {
        $downTimes = $this->makeDownTimes(10);
        $this->assertEquals(count($downTimes), 10);

        $dsDownTimes = new DSDownTimes;

        foreach ($downTimes as $dsDownTime) {
            $this->assertInstanceOf(DSDownTime::class, $dsDownTime);

            $dsDownTimes[] = $dsDownTime;
        }

        $this->assertEquals(count($downTimes), count($dsDownTimes));

        unset($dsDownTimes[1]);

        $counter = 0;
        foreach ($dsDownTimes as $dsDownTime) {
            $this->assertInstanceOf(DSDownTime::class, $dsDownTime);

            $counter++;
        }

        $this->assertEquals(count($downTimes) - 1, $counter);
    }

    private function makeDownTimes(int $downTimesCount): array
    {
        $downTimes = [];

        for ($i = 1; $i <= $downTimesCount; $i++) {
            /** @var \TheClinic\DataStructures\Time\DSDownTime|\Mockery\MockInterface $dsDownTime */
            $dsDownTime = Mockery::mock(DSDownTime::class);
            $dsDownTime->shouldReceive("getStart")->andReturn((new \DateTime(($i * 2) . ":00:00")));
            $dsDownTime->shouldReceive("getStartTimestamp")->andReturn((new \DateTime(($i * 2) . ":00:00"))->getTimestamp());
            $dsDownTime->shouldReceive("getEnd")->andReturn((new \DateTime(($i * 2) . ":00:00"))->modify("+30 minute"));
            $dsDownTime->shouldReceive("getEndTimestamp")->andReturn((new \DateTime(($i * 2) . ":00:00"))->modify("+30 minute")->getTimestamp());

            $downTimes[] = $dsDownTime;
        }

        return $downTimes;
    }

    private function makeCustomDownTimes(string $start, string $end): DSDownTime
    {
        /** @var \TheClinic\DataStructures\Time\DSDownTime|\Mockery\MockInterface $dsDownTime */
        $dsDownTime = Mockery::mock(DSDownTime::class);
        $dsDownTime->shouldReceive("getStart")->andReturn((new \DateTime($start)));
        $dsDownTime->shouldReceive("getStartTimestamp")->andReturn((new \DateTime($start))->getTimestamp());
        $dsDownTime->shouldReceive("getEnd")->andReturn((new \DateTime($end)));
        $dsDownTime->shouldReceive("getEndTimestamp")->andReturn((new \DateTime($end))->getTimestamp());

        return $dsDownTime;
    }
}
