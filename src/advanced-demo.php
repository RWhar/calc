<?php

declare(strict_types=1);

use Vendor\PrecisionMaths\PrecisionMath;

require_once __DIR__ . '/../vendor/autoload.php';

class PrecisionPolicies
{
    public const int LINE_DISCOUNT = PrecisionMath::ROUND_FLOOR;
    public const int TOTAL_DISCOUNT = PrecisionMath::ROUND_HALF_UP;
    public const int SUB_TOTAL = PrecisionMath::ROUND_FLOOR;
    public const int TOTAL_DISCOUNT_PCT = PrecisionMath::ROUND_HALF_UP;

    public function get(string $name): int
    {
        if (! defined(self::class . '::' . $name)) {
            throw new LogicException('Undefined precision policy: ' . $name);
        }

        return constant(self::class . '::' . $name);
    }
}

class ScalePolicies
{
    public const int LINE_DISCOUNT = 4;
    public const int TOTAL_DISCOUNT = 2;
    public const int SUB_TOTAL = 2;
    public const int TOTAL_DISCOUNT_PCT = 2;
    public const int TOTAL = 2;

    public function get(string $name): int
    {
        if (! defined(self::class . '::' . $name)) {
            throw new LogicException('Undefined scale policy: ' . $name);
        }

        return constant(self::class . '::' . $name);
    }
}
