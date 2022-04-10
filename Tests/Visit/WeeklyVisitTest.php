<?php

namespace Tests\Visit;

use Faker\Factory;
use Faker\Generator;
use Mockery;
use Mockery\MockInterface;
use Tests\Fakers\Time\DSDownTimesFaker;
use Tests\Fakers\Time\DSWeekDaysPeriodsFaker;
use Tests\Fakers\Time\DSWorkScheduleFaker;
use Tests\TestCase;
use TheClinic\Visit\Utilities\SearchingBetweenDownTimes;
use TheClinic\Visit\WeeklyVisit;
use TheClinicDataStructures\DataStructures\Time\DSDownTimes;
use TheClinicDataStructures\DataStructures\Time\DSWeekDaysPeriods;
use TheClinicDataStructures\DataStructures\Time\DSWorkSchedule;
use TheClinicDataStructures\DataStructures\Visit\DSVisit;
use TheClinicDataStructures\DataStructures\Visit\DSVisits;
use TheClinicDataStructures\DataStructures\Visit\Laser\DSLaserVisit;

class WeeklyVisitTest extends TestCase
{
    private Generator $faker;

    private DSVisits $dsVisits;

    protected function setUp(): void
    {
        parent::setUp();
        $this->faker = Factory::create();

        $this->dsVisits = $this->makeFutureVisits('Natural');
    }

    public function testMultiFindVisit(): void
    {
        try {
            $visitsCount = 100;
            $futureDays = 4;

            $t = explode(' ', microtime());
            $ms = $t[0];
            $s = $t[1];

            for ($i = 0; $i < $visitsCount; $i++) {
                $consumingTime = round($this->faker->numberBetween(600, 3600), -2);

                $testStartingTime = (new \DateTime("00:00:00"));

                $dsWeekDaysPeriods = $this->makeDSWeekDaysPeriods([
                    'Monday' => [
                        ['05:00:00', '07:00:00'],
                        ['07:30:00', '09:00:00'],
                    ],
                    'Thursday' => [
                        ['05:00:00', '07:00:00'],
                        ['07:30:00', '09:00:00'],
                    ]
                ]);

                $dsDownTimes = $this->makeDSDowntimes($testStartingTime, $futureDays, 3600, 1800);
                $this->logDSDownTimes($dsDownTimes);

                $dsWorkSchedule = $this->makeDSWorkSchedule();
                $this->logDSWorkSchedule($dsWorkSchedule);

                $tt = explode(' ', microtime());
                $tms = $tt[0];
                $ts = $tt[1];
                $timestamp = $this->findVisit($testStartingTime, $dsWeekDaysPeriods, $consumingTime, $dsDownTimes, $dsWorkSchedule);
                $tt1 = explode(' ', microtime());
                $tms1 = $tt1[0];
                $ts1 = $tt1[1];

                $this->dsVisits->setSort('Natural');
                $this->addToFutureVisits($timestamp, $consumingTime, $i, ($ts1 + $tms1) - ($ts + $tms));
            }

            $this->assertCount($visitsCount, $this->dsVisits);
        } finally {
            $t1 = explode(' ', microtime());
            $ms1 = $t1[0];
            $s1 = $t1[1];

            $t = ($s1 + $ms1) - ($s + $ms);

            $this->log('Total visits: ' . strval($visitsCount) . "\n");
            $this->log('Total needed time: ' . strval($t) . "\n\n");
        }
    }

    public function testFindVisits(): void
    {
        // No gap
        $consumingTime = 1800;
        $testStartingTime = (new \DateTime("00:00:00"))->modify("+1 day");

        $dsWeekDaysPeriods = $this->makeDSWeekDaysPeriods([
            $testStartingTime->format('l') => [
                ['04:00:00', '05:00:00'],
                ['05:30:00', '07:00:00'],
            ]
        ]);

        $this->testFindVisit($testStartingTime, $dsWeekDaysPeriods, $testStartingTime->setTime(4, 0)->getTimestamp(), $consumingTime);

        $dsWeekDaysPeriods = $this->makeDSWeekDaysPeriods([
            $testStartingTime->format('l') => [
                ['05:30:00', '07:00:00'],
            ]
        ]);

        $this->testFindVisit($testStartingTime, $dsWeekDaysPeriods, $testStartingTime->setTime(5, 30)->getTimestamp(), $consumingTime);

        $dsWeekDaysPeriods = $this->makeDSWeekDaysPeriods([
            $testStartingTime->format('l') => [
                ['08:00:00', '08:30:00'],
            ]
        ]);

        $this->testFindVisit($testStartingTime, $dsWeekDaysPeriods, $testStartingTime->setTime(8, 0)->getTimestamp(), $consumingTime);

        $dsWeekDaysPeriods = $this->makeDSWeekDaysPeriods([
            $testStartingTime->format('l') => [
                ['07:00:00', '08:30:00'],
            ]
        ]);

        $this->testFindVisit($testStartingTime, $dsWeekDaysPeriods, $testStartingTime->setTime(8, 0)->getTimestamp(), $consumingTime);
    }

    private function testFindVisit(\DateTime $testStartingTime, DSWeekDaysPeriods $dsWeekDaysPeriods, int $expected, int $consumingTime = 3600): void
    {
        $dsDownTimes = new DSDownTimes;

        $timestamp = $this->findVisit($testStartingTime, $dsWeekDaysPeriods, $consumingTime, $dsDownTimes, $this->makeDSWorkSchedule());

        $this->assertIsInt($timestamp);
        $this->assertEquals($expected, $timestamp);
    }

    private function findVisit(\DateTime $testStartingTime, DSWeekDaysPeriods $dsWeekDaysPeriods, int $consumingTime, DSDownTimes $dsDownTimes, DSWorkSchedule $dsWorkSchedule): int
    {
        return (new WeeklyVisit(
            $dsWeekDaysPeriods,
            $consumingTime,
            $this->dsVisits,
            $dsWorkSchedule,
            $dsDownTimes,
            $testStartingTime,
        ))->findVisit();
    }

    private function makeFutureVisits(string $sort): DSVisits
    {
        $dsVisits = new DSVisits($sort);
        return $dsVisits;
    }

    private function addToFutureVisits(int $timestamp, int $consumingTime, int $i, float $t): void
    {
        try {
            $this->dsVisits[] = $dsVisit = $this->makeDSLaserVisit($timestamp, $consumingTime);
            $this->logVisit($dsVisit, $dsVisit->getConsumingTime(), $i, $t);
        } catch (\Throwable $th) {
            $contents = "\n"
                . '"' . (new \DateTime)->format('Y-m-d H:i:s l') . '"'
                . '    '
                . 'visit: '
                . (new \DateTime)->setTimestamp($timestamp)->format('Y-m-d H:i:s l')
                . '    '
                . 'visit_consuming_time: '
                . strval(intval($consumingTime / 60)) . ' M'
                . ' '
                . strval($consumingTime % 60) . ' S'
                // 
            ;
            $this->log($contents, false, 'dsWeeklyVisitErors.log');
            throw $th;
        }
    }

    private function makeDSLaserVisit(int $timestamp, int $consumingTime): DSLaserVisit
    {
        $dsVisit = new DSLaserVisit(
            $this->faker->numberBetween(1, 1000),
            $timestamp,
            $consumingTime,
            new \DateTime(),
            new \DateTime()
        );
        return $dsVisit;
    }

    private function makeDSDowntimes(\DateTime $now, int $days, int $downTimeGapDurationSeconds, int $downTimeDurationSeconds): DSDownTimes
    {
        return (new DSDownTimesFaker)->fakeIt();
    }

    private function makeDSWorkSchedule(): DSWorkSchedule
    {
        return (new DSWorkScheduleFaker)->fakeIt();
    }

    private function makeDSWeekDaysPeriods(array $data): DSWeekDaysPeriods
    {
        return (new DSWeekDaysPeriodsFaker)->customFakeIt($data);
    }

    private function logVisit(DSVisit $dsVisit, int $consumingTime, int $i, float $time): void
    {
        $contents =
            strval($i)
            . '        ' .
            strval($dsVisit->getVisitTimestamp())
            . '        ' .
            (new \DateTime)->setTimestamp($dsVisit->getVisitTimestamp())->format("Y-m-d H:i:s l")
            . '        ' .
            strval(intval($consumingTime / 60)) . ' M'
            . '        ' .
            strval($consumingTime % 60) . ' S'
            . '        ' .
            strval($time) . ' '
            . "\n"
            // 
        ;

        $this->log($contents);
    }

    private function logDSDownTimes(DSDownTimes $dsDownTimes): void
    {
        $counter = 0;
        $contents = "";
        /** @var DSDownTime $dsDownTime */
        foreach ($dsDownTimes as $dsDownTime) {
            $contents .= "\n" .
                strval($counter) .
                "      " .
                $dsDownTime->getStart()->format("Y-m-d H:i:s l") .
                "      " .
                $dsDownTime->getEnd()->format("Y-m-d H:i:s l")
                // 
            ;
            $counter++;
        }

        $this->log($contents, true, 'weeklyDSDownTimes.log');
    }

    private function logDSWorkSchedule(DSWorkSchedule $dsWorkSchedule): void
    {
        $counter = 0;
        $contents = "";
        /** @var DSDateTimePeriods $dsDateTimePeriods */
        foreach ($dsWorkSchedule as $weekDay => $dsDateTimePeriods) {
            $contents .= "\n" .
                strval($counter) .
                "      "
                // 
            ;
            /** @var DSDateTimePeriod $dsDateTimePeriod */
            foreach ($dsDateTimePeriods as $dsDateTimePeriod) {
                $contents .= $weekDay .
                    "      " .
                    "[" .
                    $dsDateTimePeriod->getStart()->format("Y-m-d H:i:s l") .
                    "---" .
                    $dsDateTimePeriod->getEnd()->format("Y-m-d H:i:s l") .
                    "]"
                    // 
                ;
            }

            $contents .= "\n";
            $counter++;
        }

        $this->log($contents, true, 'weeklyDSWorkSchedule.log');
    }

    private function log(string $contents, bool $overwrite = false, string $filename = 'weeklyVisitTest.log'): void
    {
        $filename = __DIR__ . '/' . $filename;
        if (!is_file($filename)) {
            fopen($filename, 'w');
        }

        if ($overwrite) {
            file_put_contents($filename, $contents);
        } else {
            $contents = file_get_contents($filename) . $contents;
            file_put_contents($filename, $contents);
        }
    }
}
