<?php

namespace TheClinic\Order;

class Order implements ICalculateOrder
{
    public function calculatePrice(): int
    {
        return 400000;
    }

    public function calculateTimeConsumption(): int
    {
        return 600;
    }
}
