<?php

namespace Tests\Visit;

use Faker\Factory;
use Faker\Generator;
use Tests\TestCase;
use Tests\Fakers\Time\DSDownTimesFaker;
use Tests\Fakers\Time\DSWorkScheduleFaker;
use TheClinicDataStructures\DataStructures\Time\DSDownTimes;
use TheClinicDataStructures\DataStructures\Time\DSWorkSchedule;
use TheClinicDataStructures\DataStructures\Visit\DSVisits;
use TheClinic\Visit\FastestVisit;
use TheClinic\Visit\Utilities\DownTime;
use TheClinic\Visit\Utilities\SearchingBetweenDownTimes;
use TheClinic\Visit\Utilities\SearchingBetweenTimeRange;
use TheClinic\Visit\Utilities\WorkSchedule;
use TheClinicDataStructures\DataStructures\Time\DSDateTimePeriod;
use TheClinicDataStructures\DataStructures\Time\DSDownTime;
use TheClinicDataStructures\DataStructures\Visit\DSVisit;
use TheClinicDataStructures\DataStructures\Visit\Laser\DSLaserVisit;

class FastestVisitTest extends TestCase
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
            $visitsCount = 500;
            $futureDays = 4;

            $t = explode(' ', microtime());
            $ms = $t[0];
            $s = $t[1];

            for ($i = 0; $i < $visitsCount; $i++) {
                $consumingTime = round($this->faker->numberBetween(600, 5400), -2);
                // $consumingTime = 3600;
                if ($i % 5 === 0) {
                    $consumingTime = 100;
                }

                $testStartingTime = (new \DateTime("00:00:00"))->modify("+1 day");
                $dsDownTimes = $this->makeDSDowntimes($testStartingTime, $futureDays, 3600, 1800);
                $this->logDSDownTimes($dsDownTimes);
                $dsWorkSchedule = $this->makeDSWorkSchedule();
                $this->logDSWorkSchedule($dsWorkSchedule);

                $tt = explode(' ', microtime());
                $tms = $tt[0];
                $ts = $tt[1];
                $timestamp = $this->findVisit($testStartingTime, $consumingTime, $dsDownTimes, $dsWorkSchedule);
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
        $futureDays = 1;

        // No gap
        $testStartingTime = (new \DateTime("00:00:00"))->modify("+1 day");
        $dsDownTimes = $this->makeDSDowntimes($testStartingTime, $futureDays, 3600, 1800);
        $expected = (new \DateTime())
            ->setTimestamp($testStartingTime->getTimestamp())
            ->modify("+" . strval($futureDays) . " days")
            // 
        ;

        $this->testFindVisit($testStartingTime, $dsDownTimes, $expected, $this->makeDSWorkSchedule());

        // A gap at start
        $testStartingTime = (new \DateTime("00:00:00"))->modify("+1 day");
        $dsDownTimes = $this->makeDSDowntimes($testStartingTime, $futureDays, 3600, 1800);
        unset($dsDownTimes[0]);
        $expected = (new \DateTime())
            ->setTimestamp($testStartingTime->getTimestamp())
            // 
        ;

        $this->testFindVisit($testStartingTime, $dsDownTimes, $expected, $this->makeDSWorkSchedule());

        // A small gap at start
        $testStartingTime = (new \DateTime("00:00:00"))->modify("+1 day");
        $dsDownTimes = $this->makeDSDowntimes($testStartingTime, $futureDays, 3600, 1800);
        $testStartingTime->modify('-10 minutes');
        $expected = (new \DateTime())
            ->setTimestamp($testStartingTime->getTimestamp())
            ->modify("+" . strval($futureDays) . " days")
            ->modify('+10 minutes')
            // 
        ;

        $this->testFindVisit($testStartingTime, $dsDownTimes, $expected, $this->makeDSWorkSchedule());

        // A gap after start
        $testStartingTime = (new \DateTime("00:00:00"))->modify("+1 day");
        $dsDownTimes = $this->makeDSDowntimes($testStartingTime, $futureDays, 3600, 1800);
        unset($dsDownTimes[2]);
        $expected = (new \DateTime())
            ->setTimestamp($testStartingTime->getTimestamp())
            ->modify('+90 minutes')
            // 
        ;

        $this->testFindVisit($testStartingTime, $dsDownTimes, $expected, $this->makeDSWorkSchedule());
    }

    private function testFindVisit(\DateTime $now, DSDownTimes $dsDownTimes, \DateTime $expected, DSWorkSchedule $dsWorkSchedule, int $consumingTime = 3600): void
    {
        $timestamp = $this->findVisit($now, $consumingTime, $dsDownTimes, $dsWorkSchedule);

        $this->assertIsInt($timestamp);
        $this->assertEquals(
            $expected->getTimestamp(),
            $timestamp,
            "now : " . (new \DateTime)->format("Y-m-d H:i:s l") . " expected is: " . $expected->format("Y-m-d H:i:s l") .
                " and actual is: " . (new \DateTime)->setTimestamp($timestamp)->format("Y-m-d H:i:s l")
        );
    }

    private function findVisit(\DateTime $now, int $consumingTime, DSDownTimes $dsDownTimes, DSWorkSchedule $dsWorkSchedule): int
    {
        return (new FastestVisit(
            $now,
            $consumingTime,
            $this->dsVisits,
            $dsWorkSchedule,
            $dsDownTimes
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
            $this->log($contents, false, 'dsVisitErors.log');
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

    private function makeDSWorkSchedule(): DSWorkSchedule
    {
        return (new DSWorkScheduleFaker())->fakeIt();
    }

    private function makeDSDowntimes(\DateTime $now, int $days, int $downTimeGapDurationSeconds, int $downTimeDurationSeconds): DSDownTimes
    {
        $pointer = (new \DateTime())->setTimestamp($now->getTimestamp());
        $limit = (new \DateTime())->setTimestamp($now->getTimestamp())->modify("+" . strval($days) . " days")->getTimestamp();

        $first = true;
        $customData = [];
        $start = (new \DateTime())->setTimestamp($pointer->getTimestamp());

        while ((new \DateTime())->setTimestamp($pointer->getTimestamp())->modify("+" . ($downTimeGapDurationSeconds + $downTimeDurationSeconds) . " seconds")->getTimestamp() <= $limit) {
            if (!$first) {
                $start = (new \DateTime())->setTimestamp($pointer->modify("+" . $downTimeGapDurationSeconds . " seconds")->getTimestamp());
            }
            $end = (new \DateTime())->setTimestamp($start->getTimestamp())->modify("+" . $downTimeDurationSeconds . " seconds");

            $customData[] = [$start, $end, $this->faker->lexify()];

            $first = false;
        }

        return (new DSDownTimesFaker($customData))->fakeIt();
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
            // 
        ;

        $contents .= ''
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

        $this->log($contents, true, 'dsDownTimes.log');
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

        $this->log($contents, true, 'dsWorkSchedule.log');
    }

    private function log(string $contents, bool $overwrite = false, string $filename = 'visitTest.log'): void
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
