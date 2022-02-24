<?php

namespace Tests\DataStructures\Time;

use Faker\Factory;
use Faker\Generator;
use Tests\TestCase;
use TheClinic\DataStructures\Time\DSDateTimePeriod;
use TheClinic\Exceptions\DataStructures\Time\TimeSequenceViolationException;

class DSDateTimePeriodTest extends TestCase
{
    private Generator $faker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->faker = Factory::create();
    }

    public function testDataStructure(): void
    {
        $start = new \DateTime();
        $end = (new \DateTime())->modify("+10 minute");

        $dsDateTimePeriod = new DSDateTimePeriod($start, $end);

        $this->assertEquals($start->getTimestamp(), $dsDateTimePeriod->getStartTimestamp());
        $this->assertEquals($end->getTimestamp(), $dsDateTimePeriod->getEndTimestamp());

        try {
            $dsDateTimePeriod->setEnd((new \DateTime())->setTimestamp($end->getTimestamp())->modify("-11 minute"));

            throw new \LogicException("We can't have the end date time sooner than the start date time.", 500);
        } catch (TimeSequenceViolationException $th) {
        }

        $this->assertEquals($end->getTimestamp(), $dsDateTimePeriod->getEndTimestamp());

        try {
            $dsDateTimePeriod->setStart((new \DateTime())->setTimestamp($start->getTimestamp())->modify("+11 minute"));

            throw new \LogicException("We can't have the start date time further than the end date time.", 500);
        } catch (TimeSequenceViolationException $th) {
        }

        try {
            new DSDateTimePeriod(new \DateTime(), (new \DateTime())->modify("-1 minute"));

            throw new \LogicException("We can't have the end date time sooner than the start date time.", 500);
        } catch (TimeSequenceViolationException $th) {
        }

        $this->assertEquals($start->getTimestamp(), $dsDateTimePeriod->getStartTimestamp());
    }
}
