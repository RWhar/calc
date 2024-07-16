<?php

declare(strict_types=1);

namespace Vendor\PrecisionMaths;

use LogicException;
use ValueError;

class PrecisionMath
{
    /** @var int comparison delta representing less than */
    public const int COMP_LT = -1;

    /** @var int comparison delta representing equal */
    public const int COMP_EQ = 0;

    /** @var int comparison delta representing greater than */
    public const int COMP_GT = 1;

    /** @var int high scale value to avoid unintended precision loss */
    public const int INTERNAL_PRECISION = 100;

    public const int ROUND_HALF_UP = 1;
    public const int ROUND_FLOOR = 6;

    public function __construct(
        protected readonly ?int $forceMinFormatScale = 0,
        protected readonly ?bool $useFloatingPrecisionDecimals = false
    ) {

    }

    /**
     * Decorates bcadd(), increasing the scale default to INTERNAL_PRECISION
     *
     * @return string the sum
     */
    public function add(string $leftOperand, string $rightOperand, ?int $scale = self::INTERNAL_PRECISION): string
    {
        return bcadd($leftOperand, $rightOperand, $scale);
    }

    /**
     * Decorates bcsub(), increasing the scale default to INTERNAL_PRECISION
     *
     * @return string the difference
     */
    public function sub(string $leftOperand, string $rightOperand, ?int $scale = self::INTERNAL_PRECISION): string
    {
        return bcsub($leftOperand, $rightOperand, $scale);
    }

    /**
     * Decorates bcmul(), increasing the scale default to INTERNAL_PRECISION
     *
     * @return string the product
     */
    public function mul(string $leftOperand, string $rightOperand, ?int $scale = self::INTERNAL_PRECISION): string
    {
        return bcmul($leftOperand, $rightOperand, $scale);
    }

    /**
     * Decorates bcdiv(), increasing the scale default to INTERNAL_PRECISION
     *
     * @return string the quotient
     */
    public function div(string $leftOperand, string $rightOperand, ?int $scale = self::INTERNAL_PRECISION): string
    {
        return bcdiv($leftOperand, $rightOperand, $scale);
    }

    /**
     * Decorates bcmod(), increasing the scale default to INTERNAL_PRECISION
     *
     * @return string the remainder
     */
    public function mod(string $leftOperand, string $rightOperand, ?int $scale = self::INTERNAL_PRECISION): string
    {
        return bcmod($leftOperand, $rightOperand, $scale);
    }

    /**
     * Decorates bcpow(), increasing the scale default to INTERNAL_PRECISION
     *
     * @return string the result
     */
    public function pow(string $base, string $exponent, ?int $scale = self::INTERNAL_PRECISION): string
    {
        return bcpow($base, $exponent, $scale);
    }

    /**
     * Decorates bccomp(), increasing the scale default to INTERNAL_PRECISION
     *
     * @return int the delta of the left operand -1 === lt, 0 === eq, 1 === gt
     */
    public function comp(string $leftOperand, string $rightOperand, ?int $scale = self::INTERNAL_PRECISION): int
    {
        return bccomp($leftOperand, $rightOperand, $scale);
    }

    /**
     * @return bool true if the left operand is less than the right
     */
    public function lt(string $leftOperand, string $rightOperand, ?int $scale = self::INTERNAL_PRECISION): bool
    {
        return bccomp($leftOperand, $rightOperand, $scale) === static::COMP_LT;
    }

    /**
     * @return bool true if the left operand is equal to the right
     */
    public function eq(string $leftOperand, string $rightOperand, ?int $scale = self::INTERNAL_PRECISION): bool
    {
        return bccomp($leftOperand, $rightOperand, $scale) === static::COMP_EQ;
    }

    /**
     * @return bool true if the left operand is greater than the right
     */
    public function gt(string $leftOperand, string $rightOperand, ?int $scale = self::INTERNAL_PRECISION): bool
    {
        return bccomp($leftOperand, $rightOperand, $scale) === static::COMP_GT;
    }

    /**
     * @return bool true if the left operand is less than, or equal to, the right
     */
    public function lte(string $leftOperand, string $rightOperand, ?int $scale = self::INTERNAL_PRECISION): bool
    {
        return  in_array(bccomp($leftOperand, $rightOperand, $scale), [static::COMP_LT, static::COMP_EQ]);
    }

    /**
     * @return bool true if the left operand is greater than, or equal to, the right
     */
    public function gte(string $leftOperand, string $rightOperand, ?int $scale = self::INTERNAL_PRECISION): bool
    {
        return in_array(bccomp($leftOperand, $rightOperand, $scale), [static::COMP_GT, static::COMP_EQ]);
    }

    /**
     * @return bool true if the number is less than zero
     */
    public function isNegative(string $number, ?int $scale = self::INTERNAL_PRECISION): bool
    {
        return bccomp($number, '0', $scale) === static::COMP_LT;
    }

    /**
     * @return bool true if the number contains a period; expects UK locale
     */
    public function hasDecimal(string $number, ?int $scale = self::INTERNAL_PRECISION): bool
    {
        return str_contains($number, '.');
    }

    /**
     * Rounds the number to $scale decimals, using the HALF_UP rounding strategy by default
     */
    public function round(string $number, int $scale, int $roundingStrategy = self::ROUND_HALF_UP): string
    {
        return match ($roundingStrategy) {
            static::ROUND_HALF_UP => $this->roundHalfUp($number, $scale),
            static::ROUND_FLOOR => $this->roundFloor($number, $scale),
            default => throw new LogicException('Unknown or unimplemented rounding strategy specified')
        };
    }

    /**
     * Round number to the specified decimal accuracy.
     *
     * Rounds the number away from zero when it is halfway there, making 1.15 into 1.2 and -1.15 into -1.2.
     *
     * @return string number
     */
    public function roundHalfUp(string $number, int $scale): string
    {
        $exponent = bcpow('10', (string) ($scale + 1), 0);

        $value = bcmul($number, $exponent,0);

        if (static::isNegative($number)) {
            return bcdiv(bcadd($value, '-5', 0), $exponent, $scale);
        }

        return bcdiv(bcadd($value, '5', 0), $exponent, $scale);
    }

    /**
     * Round number to the specified decimal accuracy.
     *
     * TODO: Add description of strategy
     *
     * @return string number
     */
    public function roundFloor(string $number, int $scale): string
    {
        if (! $this->hasDecimal($number)) {
            return $number;
        }

        $exponent = bcpow('10', (string) ($scale + 1), 0);

        $value = bcmul($number, $exponent,0);

        if (static::isNegative($number)) {
            return bcdiv(bcadd($value, '-10', static::INTERNAL_PRECISION), $exponent, $scale);
        }

        return bcdiv($value, $exponent, $scale);
    }

    /**
     * @return string the first whole number less than or equal to the number
     */
    public function floor(string $number): string
    {
        if (! static::hasDecimal($number)) {
            return $number;
        }

        if (static::isNegative($number)) {
            return bcsub($number, '1', 0);
        }

        return bcadd($number, '0', 0);
    }

    /**
     * @return string the first whole number greater than or equal to the number
     */
    public function ceiling(string $number): string
    {
        if (! static::hasDecimal($number)) {
            return $number;
        }

        if (static::isNegative($number)) {
            return bcadd($number, '0', 0);
        }

        $floor = bcadd($number, '0', 0);

        return bcadd($floor, '1', 0);
    }

    /**
     * @return string the absolute value
     */
    public function abs(string $number): string
    {
        if (! static::isNegative($number)) {
            return $number;
        }

        return ltrim($number, '-');
    }

    /**
     * @return string the number without any trailing decimal zeros
     */
    public function floatingDecimalPrecision(string $number): string
    {
        if (! static::hasDecimal($number)) {
            return $number;
        }

        return rtrim(rtrim($number, '0'), '.');
    }

    /**
     * @param array<int, string> $numbers an array of string typed numbers
     * @param bool $descending defaults to false
     * @return array<int, string> the input number array, sorted numerically
     */
    public function sort(array $numbers, ?bool $descending = false): array
    {
        if ($descending === true) {
            rsort($numbers, SORT_NUMERIC);
        } else {
            sort($numbers, SORT_NUMERIC);
        }

        return $numbers;
    }

    /**
     * @return string the lowest number in the provided set
     */
    public function min(array $numbers): string
    {
        if (empty($numbers)) {
            throw new ValueError('value must contain at least one element');
        }

        $ordered = $this->sort($numbers);

        return $ordered[0];
    }

    /**
     * @return string the highest number in the provided set
     */
    public function max(array $numbers): string
    {
        if (empty($numbers)) {
            throw new ValueError('value must contain at least one element');
        }

        $ordered = $this->sort($numbers, true);

        return $ordered[0];
    }

    /**
     * @return string the sum of all numbers in the provided set
     */
    public function sum(array $numbers): string
    {
        if (empty($numbers)) {
            throw new ValueError('value must contain at least one element');
        }

        $accumulation = '0';

        foreach ($numbers as $number) {
            $accumulation = bcadd($accumulation, $number, self::INTERNAL_PRECISION);
        }

        return $this->floatingDecimalPrecision($accumulation);
    }
}
