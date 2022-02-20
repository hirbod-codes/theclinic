<?php

namespace TheClinic\Order;

use TheClinic\Order\ICalculateOrder;

interface ICalculateLaserOrder extends ICalculateOrder
{
    public function calculatePriceWithoutDiscount(): int;
}
