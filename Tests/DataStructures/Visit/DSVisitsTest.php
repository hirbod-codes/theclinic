<?php

namespace TheClinic\Tests\DataStructures\Visit;

use Faker\Factory;
use Faker\Generator;
use Mockery;
use PHPUnit\Framework\TestCase;
use TheClinic\DataStructures\Visit\DSVisit;
use TheClinic\DataStructures\Visit\DSVisits;
use TheClinic\Exceptions\DataStructures\Visit\VisitExceptions;

class DSVisitsTest extends TestCase
{
    private Generator $faker;

    protected function setUp(): void
    {
        $this->faker = Factory::create();
    }

    public function testDataStructure(): void
    {
        $visitCount = 7;

        $this->testDSVisits($visitCount, "ASC");
        $this->testDSVisits($visitCount, "DESC");
        $this->testDSVisits($visitCount, "Natural");
    }

    /**
     * @param integer $visitCount This variable must have the value of at least 5.
     * @param string $sort one of the following "ASC", "DESC", "Natural".
     * @return void
     */
    private function testDSVisits(int $visitCount, string $sort): void
    {
        if (!in_array($sort, ["ASC", "DESC", "Natural"])) {
            throw new \Exception("The value of \$sort is not acceptable", 1);
        }

        $this->testVisitsArrayAccessInterface($visitCount, $sort);
        $this->testVisitsIteratorInterface($visitCount, $sort);
    }

    private function testVisitsArrayAccessInterface(int $visitCount, string $sort): void
    {
        $this->testArrayAccessWithNullOffset($visitCount, $sort);
        $this->testArrayAccessWithIntegerOffset($visitCount, $sort);
    }

    private function testVisitsIteratorInterface(int $visitCount, string $sort): void
    {
        $pointer = new \DateTime();
        /** @var array $visits */
        $visits = $this->makeVisits($pointer, $sort, $visitCount);

        $dsVisits = new DSVisits($sort);
        foreach ($visits as $visit) {
            $dsVisits[] = $visit;
        }

        $this->assertEquals(count($visits), count($dsVisits));

        $dsVisits = new DSVisits($sort);

        $counter = 0;
        foreach ($dsVisits as $key => $visit) {
            if (!($visit instanceof DSVisit)) {
                throw new \RuntimeException("visits must be of type: " . DSVisit::class . ".", 500);
            }

            $dsVisits[] = $visit;

            $counter++;
        }

        $this->assertEquals($counter, count($dsVisits));
    }

    private function testArrayAccessWithNullOffset(int $visitCount, string $sort): void
    {
        $pointer = new \DateTime();
        /** @var array $visits */
        $visits = $this->makeVisits($pointer, $sort, $visitCount);

        $dsVisits = new DSVisits($sort);
        foreach ($visits as $visit) {
            $dsVisits[] = $visit;
        }

        $this->assertEquals(count($visits), count($dsVisits));

        switch ($sort) {
            case 'ASC':
                $this->testASCNullOffset($pointer, $dsVisits);
                break;

            case 'DESC':
                $this->testDESCNullOffset($pointer, $dsVisits);
                break;

            case 'Natural':
                $this->TestNaturalNullOffset($pointer, $dsVisits);
                break;

            default:
                break;
        }
    }

    private function testArrayAccessWithIntegerOffset(int $visitCount, string $sort): void
    {
        $pointer = new \DateTime();
        /** @var array $visits */
        $visits = $this->makeVisits($pointer, $sort, $visitCount);

        $dsVisits = new DSVisits($sort);
        for ($i = 0; $i < $visitCount; $i++) {
            $dsVisits[$i] = $visits[$i];
        }

        $this->assertEquals(count($visits), count($dsVisits));

        switch ($sort) {
            case 'ASC':
                $this->testASCIntegerOffset($pointer, $dsVisits);
                break;

            case 'DESC':
                $this->testDESCIntegerOffset($pointer, $dsVisits);
                break;

            case 'Natural':
                $this->TestNaturalIntegerOffset($pointer, $dsVisits);
                break;

            default:
                break;
        }
    }

    private function testASCIntegerOffset(\DateTime $pointer, DSVisits $dsVisits): void
    {
        /** @var array|DSVisits $dsVisits */
        $dsVisits[$dsVisits->findLastPosition() + 1] = $this->makeCustomVisit((new \DateTime())->setTimestamp($pointer->getTimestamp())->modify("+30 minutes")->getTimestamp(), 1800);

        $dsVisits[0] = $this->makeCustomVisit($dsVisits[0]->getVisitTimestamp() + 10, 1800);

        unset($dsVisits[1]);
        unset($dsVisits[3]);

        $dsVisits[2] = $this->makeCustomVisit((new \DateTime())->setTimestamp($dsVisits[2]->getVisitTimestamp())->modify("-10 minutes")->getTimestamp(), 1800);

        try {
            $dsVisits[3] = $this->makeCustomVisit((new \DateTime())->setTimestamp($dsVisits[4]->getVisitTimestamp())->modify("-5 minutes")->getTimestamp(), 1800);
            throw new \RuntimeException("", 500);
        } catch (VisitExceptions $th) {
        }

        try {
            $dsVisits[3] = $this->makeCustomVisit((new \DateTime())->setTimestamp($dsVisits[4]->getVisitTimestamp())->modify("+5 minutes")->getTimestamp(), 1200);
            throw new \RuntimeException("", 500);
        } catch (VisitExceptions $th) {
        }
    }

    private function testDESCIntegerOffset(\DateTime $pointer, DSVisits $dsVisits): void
    {
        /** @var array|DSVisits $dsVisits */
        $dsVisits[$dsVisits->findLastPosition() + 1] = $this->makeCustomVisit((new \DateTime())->setTimestamp($pointer->getTimestamp())->modify("-30 minutes")->getTimestamp(), 1800);

        $dsVisits[0] = $this->makeCustomVisit($dsVisits[0]->getVisitTimestamp() - 600, 1800);

        unset($dsVisits[1]);
        unset($dsVisits[3]);

        $dsVisits[2] = $this->makeCustomVisit((new \DateTime())->setTimestamp($dsVisits[2]->getVisitTimestamp())->modify("-10 minutes")->getTimestamp(), 1800);

        try {
            $dsVisits[3] = $this->makeCustomVisit((new \DateTime())->setTimestamp($dsVisits[2]->getVisitTimestamp())->modify("-5 minutes")->getTimestamp(), 1800);
            throw new \RuntimeException("", 500);
        } catch (VisitExceptions $th) {
        }

        try {
            $dsVisits[3] = $this->makeCustomVisit((new \DateTime())->setTimestamp($dsVisits[2]->getVisitTimestamp())->modify("+5 minutes")->getTimestamp(), 1200);
            throw new \RuntimeException("", 500);
        } catch (VisitExceptions $th) {
        }
    }

    private function TestNaturalIntegerOffset(\DateTime $pointer, DSVisits $dsVisits): void
    {
        $key = $this->faker->numberBetween(0, count($dsVisits) - 2);

        $dsVisits[$key] = $this->makeCustomVisit((new \DateTime())->setTimestamp($dsVisits[$key]->getVisitTimestamp())->getTimestamp(), 1800);

        try {
            $dsVisits[$key] = $this->makeCustomVisit((new \DateTime())->setTimestamp($dsVisits[$key + 1]->getVisitTimestamp())->modify("-29 minutes")->getTimestamp(), 1800);

            throw new \RuntimeException("", 500);
        } catch (VisitExceptions $th) {
        }
    }

    private function TestASCNullOffset(\DateTime $pointer, DSVisits $dsVisits): void
    {
        try {
            $dsVisits[] = $this->makeCustomVisit((new \DateTime())->setTimestamp($pointer->getTimestamp())->modify("-30 minutes")->getTimestamp(), 1800);

            throw new \RuntimeException("", 500);
        } catch (VisitExceptions $th) {
        }

        try {
            $dsVisits[] = $this->makeCustomVisit((new \DateTime())->setTimestamp($pointer->getTimestamp())->modify("+1 minute")->getTimestamp(), 1800);

            throw new \RuntimeException("", 500);
        } catch (VisitExceptions $th) {
        }

        $dsVisits[] = $this->makeCustomVisit((new \DateTime())->setTimestamp($pointer->getTimestamp())->modify("+30 minute")->getTimestamp(), 1800);
    }

    private function TestDESCNullOffset(\DateTime $pointer, DSVisits $dsVisits): void
    {
        try {
            $dsVisits[] = $this->makeCustomVisit((new \DateTime())->setTimestamp($pointer->getTimestamp())->modify("+30 minutes")->getTimestamp(), 1800);

            throw new \RuntimeException("", 500);
        } catch (VisitExceptions $th) {
        }

        try {
            $dsVisits[] = $this->makeCustomVisit((new \DateTime())->setTimestamp($pointer->getTimestamp())->modify("-29 minute")->getTimestamp(), 1800);

            throw new \RuntimeException("", 500);
        } catch (VisitExceptions $th) {
        }

        $dsVisits[] = $this->makeCustomVisit((new \DateTime())->setTimestamp($pointer->getTimestamp())->modify("-30 minute")->getTimestamp(), 1800);
    }

    private function TestNaturalNullOffset(\DateTime $pointer, DSVisits $dsVisits): void
    {
        try {
            $dsVisits[] = $this->makeCustomVisit((new \DateTime())->setTimestamp($pointer->getTimestamp())->modify("+1 minute")->getTimestamp(), 1800);

            throw new \RuntimeException("", 500);
        } catch (VisitExceptions $th) {
        }

        try {
            $dsVisits[] = $this->makeCustomVisit((new \DateTime())->setTimestamp($pointer->getTimestamp())->modify("-29 minute")->getTimestamp(), 1800);

            throw new \RuntimeException("", 500);
        } catch (VisitExceptions $th) {
        }

        $dsVisits[] = $this->makeCustomVisit((new \DateTime())->setTimestamp($pointer->getTimestamp())->modify("-30 minute")->getTimestamp(), 1800);
        $dsVisits[] = $this->makeCustomVisit((new \DateTime())->setTimestamp($pointer->getTimestamp())->modify("+30 minute")->getTimestamp(), 1800);
    }

    private function makeVisits(\DateTime &$pointer, string $sort, int $visitCount): array
    {
        $visits = [];

        for ($i = 0; $i < $visitCount; $i++) {
            switch ($sort) {
                case 'ASC':
                    $visits[] = $this->makeAscendingVisit($pointer);
                    break;

                case 'DESC':
                    $visits[] = $this->makeDescendingVisit($pointer);
                    break;

                case 'Natural':
                    $visits[] = $this->makeRandomVisit($pointer, $i);
                    break;

                default:
                    break;
            }
        }

        return $visits;
    }

    private function makeCustomVisit(int $visitTimestamp, int $consumingTime): DSVisit
    {
        /** @var DSVisit|\Mockery\MockInterface $visit */
        $visit = Mockery::mock(DSVisit::class);

        $visit->shouldReceive("getVisitTimestamp")->andReturn($visitTimestamp);
        $visit->shouldReceive("getConsumingTime")->andReturn($consumingTime);

        return $visit;
    }

    private function makeRandomVisit(\DateTime &$pointer, int $callsCount): DSVisit
    {
        /** @var DSVisit|\Mockery\MockInterface $visit */
        $visit = Mockery::mock(DSVisit::class);

        if (($callsCount + 1) % 2 === 0) {
            $visit->shouldReceive("getVisitTimestamp")->andReturn($pointer->modify("+" . (2 * ($callsCount + 1)) . " hours")->getTimestamp());
        } else {
            $visit->shouldReceive("getVisitTimestamp")->andReturn($pointer->modify("-" . (2 * ($callsCount + 1)) . " hours")->getTimestamp());
        }

        $visit->shouldReceive("getConsumingTime")->andReturn(1800);

        return $visit;
    }

    private function makeAscendingVisit(\DateTime &$pointer): DSVisit
    {
        /** @var DSVisit|\Mockery\MockInterface $visit */
        $visit = Mockery::mock(DSVisit::class);
        $visit->shouldReceive("getVisitTimestamp")->andReturn($pointer->modify("+2 hours")->getTimestamp());
        $visit->shouldReceive("getConsumingTime")->andReturn(1800);

        return $visit;
    }

    private function makeDescendingVisit(\DateTime &$pointer): DSVisit
    {
        /** @var DSVisit|\Mockery\MockInterface $visit */
        $visit = Mockery::mock(DSVisit::class);
        $visit->shouldReceive("getVisitTimestamp")->andReturn($pointer->modify("-150 minutes")->getTimestamp());
        $visit->shouldReceive("getConsumingTime")->andReturn(1800);

        return $visit;
    }
}
