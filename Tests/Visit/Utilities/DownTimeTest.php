<?php

namespace Tests\Visit\Utilities;

use Faker\Factory;
use Faker\Generator;
use Tests\TestCase;
use Tests\Fakers\Time\DSDownTimesFaker;
use TheClinicDataStructures\DataStructures\Time\DSDownTime;
use TheClinicDataStructures\DataStructures\Time\DSDownTimes;
use TheClinic\Visit\Utilities\DownTime;

class DownTimeTest extends TestCase
{
    private Generator $faker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->faker = Factory::create();
    }

    private function makeDSDownTimes(): DSDownTimes
    {
        $customData = [];
        for ($i = 6; $i < 10; $i++) {
            $customData[] = [new \DateTime("2020-10-5" . strval($i * 2) . ":00:00"), new \DateTime("2020-10-5" . strval($i * 2) . ":30:00"), $this->faker->lexify()];
        }

        return (new DSDownTimesFaker($customData))->fakeIt();
    }

    public function testMoveTimeToClosestUpTime(): void
    {
        $dsDownTimes = $this->makeDSDownTimes();

        $time = new \DateTime("2020-10-5 12:20:00");
        (new DownTime)->moveTimeToClosestUpTime($time, $dsDownTimes);
        $this->assertEquals((new \DateTime("2020-10-5 12:30:00"))->format("Y-m-d H:i:s l"), $time->format("Y-m-d H:i:s l"));

        $time = new \DateTime("2020-10-5 14:20:00");
        (new DownTime)->moveTimeToClosestUpTime($time, $dsDownTimes);
        $this->assertEquals((new \DateTime("2020-10-5 14:30:00"))->getTimestamp(), $time->getTimestamp());

        $time = new \DateTime("2020-10-5 18:20:00");
        (new DownTime)->moveTimeToClosestUpTime($time, $dsDownTimes);
        $this->assertEquals((new \DateTime("2020-10-5 18:30:00"))->getTimestamp(), $time->getTimestamp());

        $time = new \DateTime("2020-10-5 18:00:00");
        (new DownTime)->moveTimeToClosestUpTime($time, $dsDownTimes);
        $this->assertEquals((new \DateTime("2020-10-5 18:30:00"))->getTimestamp(), $time->getTimestamp());
    }

    public function testIsInDownTime(): void
    {
        $dsDownTimes = $this->makeDSDownTimes();

        $time = new \DateTime("2020-10-5 14:20:00");
        $this->assertEquals(true, (new DownTime)->isInDownTime($time, $dsDownTimes));

        $time = new \DateTime("2020-10-5 14:00:00");
        $this->assertEquals(true, (new DownTime)->isInDownTime($time, $dsDownTimes));

        $time = new \DateTime("2020-10-5 14:30:00");
        $this->assertEquals(false, (new DownTime)->isInDownTime($time, $dsDownTimes));

        $time = new \DateTime("2020-10-5 18:30:00");
        $this->assertEquals(false, (new DownTime)->isInDownTime($time, $dsDownTimes));

        $time = new \DateTime("2020-10-5 18:00:00");
        $this->assertEquals(true, (new DownTime)->isInDownTime($time, $dsDownTimes));

        $time = new \DateTime("2020-10-5 18:20:00");
        $this->assertEquals(true, (new DownTime)->isInDownTime($time, $dsDownTimes));
    }

    public function testFindDownTimeIntruptionWithTimeRange(): void
    {
        $dsDownTimes = $this->makeDSDownTimes();

        $firstDT = new \DateTime("2020-10-5 12:00:00");
        $lastDT = new \DateTime("2020-10-5 18:30:00");
        $intruptinDowTimes = (new DownTime)->findDownTimeIntruptionWithTimeRange($firstDT->getTimestamp(), $lastDT->getTimestamp(), $dsDownTimes);
        $this->assertInstanceOf(DSDownTimes::class, $intruptinDowTimes);
        $this->assertCount(4, $intruptinDowTimes);
        for ($i = 0; $i < 4; $i++) {
            $this->assertInstanceOf(DSDownTime::class, $dsDownTimes[$i]);
            $this->assertEquals(strval(2 * ($i + 6)) . ":00:00", $dsDownTimes[$i]->getStart()->format("H:i:s"));
            $this->assertEquals(strval(2 * ($i + 6)) . ":30:00", $dsDownTimes[$i]->getEnd()->format("H:i:s"));
        }

        $firstDT = new \DateTime("2020-10-5 12:20:00");
        $lastDT = new \DateTime("2020-10-5 18:20:00");
        $intruptinDowTimes = (new DownTime)->findDownTimeIntruptionWithTimeRange($firstDT->getTimestamp(), $lastDT->getTimestamp(), $dsDownTimes);
        $this->assertInstanceOf(DSDownTimes::class, $intruptinDowTimes);
        $this->assertCount(4, $intruptinDowTimes);
        for ($i = 0; $i < 4; $i++) {
            $this->assertInstanceOf(DSDownTime::class, $dsDownTimes[$i]);
            $this->assertEquals(strval(2 * ($i + 6)) . ":00:00", $dsDownTimes[$i]->getStart()->format("H:i:s"));
            $this->assertEquals(strval(2 * ($i + 6)) . ":30:00", $dsDownTimes[$i]->getEnd()->format("H:i:s"));
        }

        $firstDT = new \DateTime("2020-10-5 12:20:00");
        $lastDT = new \DateTime("2020-10-5 18:00:00");
        $intruptinDowTimes = (new DownTime)->findDownTimeIntruptionWithTimeRange($firstDT->getTimestamp(), $lastDT->getTimestamp(), $dsDownTimes);
        $this->assertInstanceOf(DSDownTimes::class, $intruptinDowTimes);
        $this->assertCount(3, $intruptinDowTimes);
        for ($i = 0; $i < 3; $i++) {
            $this->assertInstanceOf(DSDownTime::class, $dsDownTimes[$i]);
            $this->assertEquals(strval(2 * ($i + 6)) . ":00:00", $dsDownTimes[$i]->getStart()->format("H:i:s"));
            $this->assertEquals(strval(2 * ($i + 6)) . ":30:00", $dsDownTimes[$i]->getEnd()->format("H:i:s"));
        }
    }
}
