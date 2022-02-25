<?php

namespace Tests\Fakers\Order;

use Faker\Factory;
use Faker\Generator;
use TheClinicDataStructure\DataStructures\Order\DSPackages;
use TheClinicDataStructure\DataStructures\Order\DSParts;

class DSPackagesFaker
{
    private Generator $faker;

    private null|array $packages;

    private null|string $gender;

    private null|int $packagesCount;

    public function __construct(
        null|string $gender = null,
        null|array $packages = null,
        null|int $packagesCount = null
    ) {
        $this->faker = Factory::create();

        $this->gender = $gender;
        $this->packagesCount = $packagesCount;
        $this->packages = $packages;
    }

    public function fakeIt(): DSPackages
    {
        $dsPackages = new DSPackages($this->gender ?: ($this->gender = $this->faker->randomElement(["Female", "Male"])));

        if (!is_null($this->packages)) {
            foreach ($this->packages as $package) {
                $dsPackages[] = $package;
            }
        } else {
            for ($i = 0; $i < $this->packagesCount ?: $this->faker->numberBetween(1, 4); $i++) {
                $dsPackages[] = (new DSPackageFaker(
                    $this->faker->numberBetween(1, 1000),
                    $this->faker->lexify(),
                    $this->gender,
                    $this->faker->numberBetween(6000000, 20000000),
                    (new DSPartsFaker($this->gender, null, $this->faker->numberBetween(1, 10)))->fakeIt(),
                    $this->faker->numberBetween(1, 10),
                    new \DateTime(),
                    new \DateTime()
                ));
            }
        }

        return $dsPackages;
    }
}
