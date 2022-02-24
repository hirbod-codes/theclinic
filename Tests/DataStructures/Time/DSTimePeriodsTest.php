<?php

namespace Tests\DataStructures\Time;

use Faker\Factory;
use Faker\Generator;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;
use TheClinic\DataStructures\Time\DSTimePeriod;
use TheClinic\DataStructures\Time\DSTimePeriods;

class DSTimePeriodsTest extends TestCase
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
        $timePeriods = $this->makeTimePeriods(10);

        $dsTimePeriods = new DSTimePeriods;

        foreach ($timePeriods as $dsTimePeriod) {
            $dsTimePeriods[] = $dsTimePeriod;
        }

        $this->assertCount(count($timePeriods), $dsTimePeriods);

        $dsTimePeriods = new DSTimePeriods;

        for ($i = 0; $i < count($timePeriods); $i++) {
            $dsTimePeriods[$i] = $timePeriods[$i];
        }

        $this->assertCount(count($timePeriods), $dsTimePeriods);

        unset($dsTimePeriods[1]);
        unset($dsTimePeriods[3]);

        $dsTimePeriods[2] = $this->makeCustomTimePeriod("5:00:00", "5:30:00");

        try {
            $dsTimePeriods[5] = $this->makeCustomTimePeriod("11:50:00", "12:20:00");
        } catch (\Throwable $th) {
        }
    }

    private function testIterator(): void
    {
        $timePeriods = $this->makeTimePeriods(10);

        $dsTimePeriods = new DSTimePeriods;

        foreach ($timePeriods as $dsTimePeriod) {
            $dsTimePeriods[] = $dsTimePeriod;
        }

        $this->assertCount(count($timePeriods), $dsTimePeriods);

        unset($dsTimePeriods[1]);

        $counter = 0;
        foreach ($dsTimePeriods as $dsTimePeriod) {
            $this->assertInstanceOf(DSTimePeriod::class, $dsTimePeriod);

            $counter++;
        }

        $this->assertEquals(count($timePeriods) - 1, $counter);
    }

    private function makeTimePeriods(int $timePeriodsCount): array
    {
        $timePeriods = [];

        for ($i = 1; $i <= $timePeriodsCount; $i++) {
            /** @var \TheClinic\DataStructures\Time\DSTimePeriod|\Mockery\MockInterface $timePeriod */
            $timePeriod = Mockery::mock(DSTimePeriod::class);
            $timePeriod->shouldReceive("getStartTimestamp")->andReturn((new \DateTime(($i * 2) . ":00:00"))->getTimestamp());
            $timePeriod->shouldReceive("getStart")->andReturn(($i * 2) . ":00:00");
            $timePeriod->shouldReceive("getEndTimestamp")->andReturn((new \DateTime(($i * 2) . ":30:00"))->getTimestamp());
            $timePeriod->shouldReceive("getEnd")->andReturn(($i * 2) . ":30:00");

            $timePeriods[] = $timePeriod;
        }

        return $timePeriods;
    }

    private function makeCustomTimePeriod(string $start, string $end): DSTimePeriod|MockInterface
    {
        /** @var \TheClinic\DataStructures\Time\DSTimePeriod|\Mockery\MockInterface $timePeriod */
        $timePeriod = Mockery::mock(DSTimePeriod::class);
        $timePeriod->shouldReceive("getStartTimestamp")->andReturn((new \DateTime($start))->getTimestamp());
        $timePeriod->shouldReceive("getStart")->andReturn($start);
        $timePeriod->shouldReceive("getEndTimestamp")->andReturn((new \DateTime($end))->getTimestamp());
        $timePeriod->shouldReceive("getEnd")->andReturn($end);

        return $timePeriod;
    }
}
