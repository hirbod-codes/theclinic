<?php

namespace Tests\Fakers\Order;

use Faker\Factory;
use Faker\Generator;
use TheClinicDataStructure\DataStructures\Order\DSParts;

class DSPartsFaker
{
    private Generator $faker;

    private string|null $gender;

    private array|null $parts;

    private int|null $partsCount;

    public function __construct(string|null $gender = null, array|null $parts = null, int|null $partsCount = null)
    {
        if (is_null($parts) && is_null($partsCount)) {
            throw new \RuntimeException("\$partsCount is required if \$parts is null.", 500);
        }

        $this->faker = Factory::create();

        $this->partsCount = $partsCount;
        $this->parts = $parts;
        $this->gender = $gender;
    }

    public function fakeIt(): DSParts
    {
        $dsParts = new DSParts($this->gender ?: $this->faker->randomElement(["Female", "Male"]));

        if (is_null($this->parts)) {
            for ($i = 0; $i < $this->partsCount; $i++) {
                $dsParts[] = (new DSPartFaker())->fakeIt();
            }
        } else {
            foreach ($this->parts as $part) {
                $dsParts[] = $part;
            }
        }

        return $dsParts;
    }
}
