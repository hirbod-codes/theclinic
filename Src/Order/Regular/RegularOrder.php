<?php

namespace TheClinic\Order\Regular;

use TheClinic\Order\ICalculateRegularOrder;

class RegularOrder implements ICalculateRegularOrder
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
