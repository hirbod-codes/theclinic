<?php

namespace Tests\Visit\Utilities;

use Faker\Factory;
use Faker\Generator;
use Tests\TestCase;
use Tests\Fakers\Time\DSWorkScheduleFaker;
use TheClinicDataStructures\DataStructures\Time\DSWorkSchedule;
use TheClinic\Visit\Utilities\WorkSchedule;

class WorkScheduleTest extends TestCase
{
    private Generator $faker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->faker = Factory::create();
    }

    private function makeDSWorkSchedule(): DSWorkSchedule
    {
        $customData = [];
        for ($i = 0; $i < 7; $i++) {
            $weekDay = DSWorkSchedule::$weekDays[$i];

            $periods = [];
            for ($j = 0; $j < 3; $j++) {
                $periods[] = [strval((2 * ($j + 5))) . ":00:00", strval((2 * ($j + 5))) . ":30:00"];
            }

            $customData[$weekDay] = $periods;
        }

        return (new DSWorkScheduleFaker($customData))->fakeIt();
    }

    public function testIsInWorkSchedule(): void
    {
        $dsWorkSchedule = $this->makeDSWorkSchedule();

        $dt = new \DateTime("09:00:00");
        $this->assertEquals(false, (new WorkSchedule)->isInWorkSchedule($dt, $dsWorkSchedule));

        $dt = new \DateTime("10:00:00");
        $this->assertEquals(true, (new WorkSchedule)->isInWorkSchedule($dt, $dsWorkSchedule));

        $dt = new \DateTime("10:20:00");
        $this->assertEquals(true, (new WorkSchedule)->isInWorkSchedule($dt, $dsWorkSchedule));

        $dt = new \DateTime("10:30:00");
        $this->assertEquals(false, (new WorkSchedule)->isInWorkSchedule($dt, $dsWorkSchedule));

        $dt = new \DateTime("10:40:00");
        $this->assertEquals(false, (new WorkSchedule)->isInWorkSchedule($dt, $dsWorkSchedule));

        $dt = new \DateTime("12:20:00");
        $this->assertEquals(true, (new WorkSchedule)->isInWorkSchedule($dt, $dsWorkSchedule));

        $dt = new \DateTime("14:30:00");
        $this->assertEquals(false, (new WorkSchedule)->isInWorkSchedule($dt, $dsWorkSchedule));
    }

    public function testMovePointerToClosestWorkSchedule(): void
    {
        $dsWorkSchedule = $this->makeDSWorkSchedule();

        $dt = new \DateTime("09:00:00");
        (new WorkSchedule)->movePointerToClosestWorkSchedule($dt, $dsWorkSchedule);
        $this->assertEquals((new \DateTime("10:00:00"))->format("Y-m-d H:i:s"), $dt->format("Y-m-d H:i:s"));

        $dt = new \DateTime("10:00:00");
        (new WorkSchedule)->movePointerToClosestWorkSchedule($dt, $dsWorkSchedule);
        $this->assertEquals((new \DateTime("10:00:00"))->format("Y-m-d H:i:s"), $dt->format("Y-m-d H:i:s"));

        $dt = new \DateTime("10:20:00");
        (new WorkSchedule)->movePointerToClosestWorkSchedule($dt, $dsWorkSchedule);
        $this->assertEquals((new \DateTime("10:20:00"))->format("Y-m-d H:i:s"), $dt->format("Y-m-d H:i:s"));

        $dt = new \DateTime("10:30:00");
        (new WorkSchedule)->movePointerToClosestWorkSchedule($dt, $dsWorkSchedule);
        $this->assertEquals((new \DateTime("12:00:00"))->format("Y-m-d H:i:s"), $dt->format("Y-m-d H:i:s"));

        $dt = new \DateTime("14:30:00");
        (new WorkSchedule)->movePointerToClosestWorkSchedule($dt, $dsWorkSchedule);
        $this->assertEquals((new \DateTime("10:00:00"))->modify('+1 day')->format("Y-m-d H:i:s"), $dt->format("Y-m-d H:i:s"));
    }
}
