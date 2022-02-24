<?php

namespace TheClinic\Order;

use TheClinic\Order\ICalculateOrder;

interface ICalculateRegularOrder extends ICalculateOrder
{
    public function calculatePrice(): int;

    public function calculateTimeConsumption(): int;
}
