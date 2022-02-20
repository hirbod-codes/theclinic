<?php

namespace Tests\DataStructures\Time;

use Faker\Factory;
use Faker\Generator;
use PHPUnit\Framework\TestCase;
use TheClinic\DataStructures\Time\DSTimePeriod;
use TheClinic\Exceptions\DataStructures\Time\TimeSequenceViolationException;

class DSTimePeriodTest extends TestCase
{
    private Generator $faker;

    protected function setUp(): void
    {
        $this->faker = Factory::create();
    }

    public function testDataStructure(): void
    {
        $dsTimePeriod = new DSTimePeriod("11:00:00", "13:00:00");

        $dsTimePeriod->setStart("10:00:00");
        $dsTimePeriod->setEnd("15:00:00");

        $this->assertEquals((new \DateTime("10:00:00", new \DateTimeZone("UTC")))->getTimestamp(), $dsTimePeriod->getStartTimestamp());
        $this->assertEquals((new \DateTime("15:00:00", new \DateTimeZone("UTC")))->getTimestamp(), $dsTimePeriod->getEndTimestamp());

        try {
            $dsTimePeriod->setStart("16:00:00");
            
            throw new \LogicException("The data structure failed to satisfy it's time order.", 500);
        } catch (TimeSequenceViolationException $th) {
        }

        try {
            $dsTimePeriod->setEnd("09:00:00");
            
            throw new \LogicException("The data structure failed to satisfy it's time order.", 500);
        } catch (TimeSequenceViolationException $th) {
        }

        $this->assertEquals((new \DateTime("10:00:00"))->getTimestamp(), $dsTimePeriod->getStartTimestamp());
        $this->assertEquals((new \DateTime("15:00:00"))->getTimestamp(), $dsTimePeriod->getEndTimestamp());
    }
}
