<?php

declare(strict_types=1);

use Vendor\PrecisionMaths\PrecisionMath;

require_once __DIR__ . '/../vendor/autoload.php';

function calculateDiscount(PrecisionMath $calc, array &$discounts): string
{
    $accumulation = '0';

    foreach ($discounts as &$period) {
        // Precision on price and rate has not been provided, it's controlled elsewhere.
        // Use the default internal precision
        $interestAmount = $calc->mul($period['price'], $period['rate']);

        // MY_POLICY_123 states use of ROUND_FLOOR strategy to four decimals on discount line items
        $interestAmount = $calc->roundFloor($interestAmount, 4);

        $period['discount_amount'] = $interestAmount;

        // MY_POLICY_123 has already rounded down to four decimals
        $accumulation = $calc->add($accumulation, $interestAmount, 4);

        unset($interestAmount);
    }

    // MY_POLICY_345 states use of ROUND_HALF_UP strategy to 2 decimals on discount totals
    return $calc->roundHalfUp($accumulation, 2);
}

function calculateSubTotal(PrecisionMath $calc, array &$discounts): string {
    // MY_POLICY_626 states use of ROUND_FLOOR strategy to 2 decimals on sub totals
    return $calc->roundFloor($calc->sum(array_column($discounts, 'price')), 2);
}

function calculateTotal(PrecisionMath $calc, string $subTotal, string $totalDiscount): string {
    // MY_POLICY_626 & MY_POLICY_345 provide the inputs with 2 decimal places
    return $calc->sub($subTotal, $totalDiscount, 2);
}

function calculateTotalDiscountPct(PrecisionMath $calc, string $subTotal, string $totalDiscount): string
{
    // MY_POLICY_969 states displayed total discount percentages use ROUND_HALF_UP to two decimal places
    $rate = $calc->roundFloor($calc->div($subTotal, $totalDiscount), 2);

//    $exponent = $calc->pow('10', '1', 0);
//    var_dump($rate, $exponent, $calc->mul($rate, $exponent, 0));

    return $calc->div('100', $rate, 2); // TODO: Fix discount - 8.8% === 0.88 min/max 0.1/0.17
}

// get the sum of discount
$discounts = [
    ['price' => '1203123', 'rate' => '0.124'],
    ['price' => '120312', 'rate' => '0.14'],
    ['price' => '1203123', 'rate' => '0.10'],
    ['price' => '10234', 'rate' => '0.17'],
    ['price' => '50435', 'rate' => '0.12'],
];

$calc = new PrecisionMath();

$subTotal = calculateSubTotal($calc, $discounts);
$totalDiscount = calculateDiscount($calc, $discounts);
$total = calculateTotal($calc, $subTotal, $totalDiscount);
$totalDiscountPercent = calculateTotalDiscountPct($calc, $subTotal, $totalDiscount);

print_r($discounts);
printf("\n");
printf("Sub Total: %s\n", number_format((float) $subTotal, 2));
printf("Discount: %s (%s%s)\n", number_format((float) $totalDiscount, 2), $totalDiscountPercent, '%');
printf("Total: %s\n", number_format((float) $total, 2));
printf("\nAdvice: %s\n", 'Due to rounding, amounts may not add to the penny. See account policy for details.');

