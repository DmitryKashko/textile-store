<?php

namespace App\Services\Orders;

/**
 * Wholesale pricing: the more metres of one fabric, the bigger the discount.
 */
class PriceCalculator
{
    /** @var array<int, int> minimum metres => discount percent, from the biggest tier */
    private const TIERS = [
        100 => 10,
        50 => 5,
    ];

    public function discountPercent(int $quantity): int
    {
        foreach (self::TIERS as $minQuantity => $percent) {
            if ($quantity >= $minQuantity) {
                return $percent;
            }
        }

        return 0;
    }

    public function lineTotal(int $unitPrice, int $quantity): int
    {
        $subtotal = $unitPrice * $quantity;

        return intdiv($subtotal * (100 - $this->discountPercent($quantity)), 100);
    }
}
