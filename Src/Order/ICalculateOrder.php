<?php

namespace TheClinic\Order;

interface ICalculateOrder
{
    public function calculatePrice(): int;

    public function calculateTimeConsumption(): int;
}
