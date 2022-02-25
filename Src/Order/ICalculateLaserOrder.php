<?php

namespace TheClinic\Order;

use TheClinicDataStructures\DataStructures\Order\DSPackages;
use TheClinicDataStructures\DataStructures\Order\DSParts;
use TheClinic\Order\ICalculateOrder;
use TheClinic\Order\Laser\ILaserPriceCalculator;
use TheClinic\Order\Laser\ILaserTimeConsumptionCalculator;

interface ICalculateLaserOrder extends ICalculateOrder
{
    public function calculatePrice(DSParts|null $parts = null, DSPackages|null $packages = null, ILaserPriceCalculator $priceCalculator): int;

    public function calculateTimeConsumption(DSParts|null $parts = null, DSPackages|null $packages = null, ILaserTimeConsumptionCalculator $timeConsumptionCalculator): int;

    public function calculatePriceWithoutDiscount(DSParts|null $parts = null, DSPackages|null $packages = null, ILaserPriceCalculator $priceCalculator): int;
}
