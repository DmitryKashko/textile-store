<?php

namespace Tests\Unit;

use App\Services\Orders\PriceCalculator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class PriceCalculatorTest extends TestCase
{
    #[DataProvider('tiers')]
    public function test_discount_depends_on_quantity(int $quantity, int $expectedPercent): void
    {
        $this->assertSame($expectedPercent, (new PriceCalculator)->discountPercent($quantity));
    }

    public static function tiers(): array
    {
        return [
            'retail' => [1, 0],
            'just below first tier' => [49, 0],
            'first tier' => [50, 5],
            'between tiers' => [99, 5],
            'second tier' => [100, 10],
            'large order' => [700, 10],
        ];
    }

    public function test_line_total_applies_discount(): void
    {
        $calculator = new PriceCalculator;

        $this->assertSame(30000, $calculator->lineTotal(15000, 2));
        // 60 m * 150.00 = 9000.00, minus 5%
        $this->assertSame(855000, $calculator->lineTotal(15000, 60));
    }

    public function test_line_total_rounds_down_to_a_kopeck(): void
    {
        // 50 * 1.99 = 99.50, minus 5% = 94.525
        $this->assertSame(9452, (new PriceCalculator)->lineTotal(199, 50));
    }
}
