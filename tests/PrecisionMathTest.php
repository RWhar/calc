<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Vendor\PrecisionMaths\PrecisionMath;

class PrecisionMathTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    public function testExtensionAvailable(): void
    {
        $msg = 'BCMath extension must be installed for the library and the tests.';

        static::assertTrue(extension_loaded('bcmath'), $msg);
    }

    #[DataProvider('provideAddition')]
    public function testItAdds(
        string $leftOperand,
        string $rightOperand,
        int $scale,
        string|Throwable $expectedResult
    ): void
    {
        $sut = new PrecisionMath();

        if ($expectedResult instanceof Throwable) {
            static::expectException(get_class($expectedResult));
        }

        $result = $sut->add($leftOperand, $rightOperand, $scale);

        $formula = static::inspectFormula($leftOperand, $rightOperand, $scale, '+', $expectedResult, $result);
        static::assertTrue(bccomp($expectedResult, $result, 100) === 0, $formula);
    }

    public static function provideAddition(): array
    {
        return [
            'basic_integer' => ['1', '2', 0, '3'],
            'basic_decimal' => ['1.3', '2.3', 1, '3.6'],
            'scientific_notation' => ['3.45e5', '2', 0, new ValueError()],
            'basic_negative_integer' => ['-1', '-2', 0, '-3'],
            'basic_negative_decimal' => ['-1.3', '-2.3', 1, '-3.6'],
            'limit_test' => ['9223372036854775807', '9223372036854775807', 0, '18446744073709551614'],
            'limit_negative_double_test' => ['9223372036854775807', '-9223372036854775807', 0, '0'],
            'limit_negative_test' => ['-9223372036854775807', '-9223372036854775807', 0, '-18446744073709551614'],
        ];
    }

    #[DataProvider('provideSubtraction')]
    public function testItSubtracts(
        string $leftOperand,
        string $rightOperand,
        int $scale,
        string|Throwable $expectedResult
    ): void
    {
        $sut = new PrecisionMath();

        if ($expectedResult instanceof Throwable) {
            static::expectException(get_class($expectedResult));
        }

        $result = $sut->sub($leftOperand, $rightOperand, $scale);

        $formula = static::inspectFormula($leftOperand, $rightOperand, $scale, '-', $expectedResult, $result);
        static::assertTrue(bccomp($expectedResult, $result, 100) === 0, $formula);
    }

    public static function provideSubtraction(): array
    {
        return [
            'basic_integer' => ['2', '1', 0, '1'],
            'basic_decimal' => ['2.3', '1.3', 1, '1.0'],
            'basic_negative_integer' => ['-1', '-2', 0, '1'],
            'basic_negative_decimal' => ['-1.3', '-2.3', 1, '1.0'],
            'scientific_notation' => ['3.45e5', '2', 0, new ValueError()],
            'limit_test' => ['9223372036854775807', '9223372036854775807', 0, '0'],
            'limit_negative_test' => ['9223372036854775807', '-9223372036854775807', 0, '18446744073709551614'],
            'limit_negative_double_test' => ['-9223372036854775807', '-9223372036854775807', 0, '0'],
        ];
    }

    #[DataProvider('multiplicationProvider')]
    public function testItMultiplies(
        string $leftOperand,
        string $rightOperand,
        int $scale,
        string|Throwable $expectedResult
    ): void
    {
        $sut = new PrecisionMath();

        if ($expectedResult instanceof Throwable) {
            static::expectException(get_class($expectedResult));
        }

        $result = $sut->mul($leftOperand, $rightOperand, $scale);

        $formula = static::inspectFormula($leftOperand, $rightOperand, $scale, '*', $expectedResult, $result);
        static::assertTrue(bccomp($expectedResult, $result, 100) === 0, $formula);
    }

    public static function multiplicationProvider(): array
    {
        return [
            'multiply_by_zero' => ['1', '0', 0, '0'],
            'basic_integer' => ['1', '2', 0, '2'],
            'basic_decimal' => ['2', '1.5', 1, '3.0'],
            'basic_negative_integer' => ['-1', '-2', 0, '2'],
            'basic_negative_decimal' => ['-1.3', '-2.3', 1, '2.9'],
            'precision_negative_decimal' => ['-1.3', '-2.3', 12, '2.990000000000'],
            'scientific_notation' => ['3.45e5', '2', 0, new ValueError()],
            'limit_test' => ['9223372036854775807', '9223372036854775807', 0, '85070591730234615847396907784232501249'],
            'limit_negative_test' => ['9223372036854775807', '-9223372036854775807', 0, '-85070591730234615847396907784232501249'],
            'limit_negative_double_test' => ['-9223372036854775807', '-9223372036854775807', 0, '85070591730234615847396907784232501249'],
        ];
    }

    #[DataProvider('divisionProvider')]
    public function testItDivides(
        string $leftOperand,
        string $rightOperand,
        int $scale,
        string|Throwable $expectedResult
    ): void
    {
        $sut = new PrecisionMath();

        if ($expectedResult instanceof Throwable) {
            self::expectException(get_class($expectedResult));
        }

        $result = $sut->div($leftOperand, $rightOperand, $scale);

        $formula = static::inspectFormula($leftOperand, $rightOperand, $scale, '/', $expectedResult, $result);
        static::assertTrue(bccomp($expectedResult, $result, 100) === 0, $formula);
    }

    public static function divisionProvider(): array
    {
        return [
            'divide_by_zero' => ['1', '0', 0, new DivisionByZeroError()],
            'basic_integer' => ['1', '2', 0, '0'],
            'basic_decimal' => ['1', '2', 1, '0.5'],
            'basic_negative_integer' => ['-1', '-2', 0, '0'],
            'basic_negative_decimal' => ['-1.3', '-2.3', 1, '0.5'],
            'precision_negative_decimal' => ['-1.3', '-2.3', 12, '0.565217391304'],
            'limit_test' => ['9223372036854775807', '9223372036854775807', 0, '1'],
            'limit_negative_test' => ['9223372036854775807', '-9223372036854775807', 0, '-1'],
            'limit_negative_double_test' => ['-9223372036854775807', '-9223372036854775807', 0, '1'],
        ];
    }

    protected static function inspectFormula(
        string $leftOperand,
        string $rightOperand,
        int $scale,
        string $operation,
        string $expectedResult,
        string $actualResult
    ): string
    {
        return sprintf(
            "precision: %s; %s %s %s === %s actual: %s",
            $scale,
            $leftOperand,
            $operation,
            $rightOperand,
            $expectedResult,
            $actualResult
        );
    }

    #[DataProvider('provideFloatingPrecisionDecimal')]
    public function testFloatingPrecisionDecimal(string $input, string $expectedResult): void
    {
        $sut = new PrecisionMath();

        $result = $sut->floatingDecimalPrecision($input);

        $msg = sprintf("%s, %s -> %s - %s", 'floating precision', $input, $expectedResult, $result);

        static::assertTrue(bccomp($expectedResult, $result, 100) === 0, $msg);
    }

    public static function provideFloatingPrecisionDecimal(): array
    {
        return [
            'basic_integer' => ['1.000',  '1'],
            'negative_basic_integer' => ['-1.000',  '-1'],
            'basic_decimal' => ['1.45323200000',  '1.453232'],
            'negative_basic_decimal' => ['-1.45323200000',  '-1.453232'],
            'trailing_decimal' => ['1.',  '1'],
            'negative_trailing_decimal' => ['-1.',  '-1'],
            'no_decimal' => ['1',  '1'],
            'negative_no_decimal' => ['-1',  '-1'],
        ];
    }

    #[DataProvider('provideRoundHalfUp')]
    public function testRoundHalfUp(string $input, int $scale, string $expectedResult): void
    {
        $sut = new PrecisionMath();

        $result = $sut->round($input, $scale, PHP_ROUND_HALF_UP);

        $msg = sprintf("%s %s -> %s - %s", $scale, $input, $expectedResult, $result);

        static::assertTrue(bccomp($expectedResult, $result, 100) === 0, $msg);
    }

    public static function provideRoundHalfUp(): array
    {
        return [
            'zero_0.8' => ['0.8', 0, '1'],
            'zero_1.1' => ['1.1', 0, '1'],
            'zero_1.5' => ['1.5', 0, '2'],
            'zero_1.9' => ['1.9', 0, '2'],
            'zero_-0.8' => ['-0.8', 0, '-1'],
            'zero_-1.1' => ['-1.1', 0, '-1'],
            'zero_-1.5' => ['-1.5', 0, '-2'],
            'zero_-1.0' => ['-1.9', 0, '-2'],
            'four_up' => ['1.12345', 4, '1.1235'],
            'four_down' => ['1.12344', 4, '1.1234'],
            'three_up' => ['1.12355', 3, '1.124'],
            'three_down' => ['1.12345', 3, '1.123'],
            'four_negative' => ['-1.12345', 4, '-1.1235'],
            'three_negative' => ['-1.12345', 3, '-1.123'],
        ];
    }

    #[DataProvider('provideRoundFloor')]
    public function testRoundFloor(string $input, int $scale, string $expectedResult): void
    {
        $sut = new PrecisionMath();

        $result = $sut->round($input, $scale, PrecisionMath::ROUND_FLOOR);

        $msg = sprintf("(%s) %s -> %s (actual: %s)", $scale, $input, $expectedResult, $result);

        static::assertTrue(strcmp($expectedResult, $result) === 0, $msg);
    }

    public static function provideRoundFloor(): array
    {
        return [
            'no_decimal' => ['1', 0, '1'],
            'no_decimal_scale_increase' => ['1', 4, '1'],
            'zero_0.8' => ['0.8', 0, '0'],
            'zero_1.1' => ['1.1', 0, '1'],
            'zero_1.5' => ['1.5', 0, '1'],
            'zero_1.9' => ['1.9', 0, '1'],
            'zero_-0.8' => ['-0.8', 0, '-1'],
            'zero_-1.1' => ['-1.1', 0, '-2'],
            'zero_-1.5' => ['-1.5', 0, '-2'],
            'zero_-1.0' => ['-1.9', 0, '-2'],
            'decimal_floor' => ['1.123455', 4, '1.1234'],
            'negative_decimal_floor' => ['-1.123455', 4, '-1.1235'],
            'decimal_floor_2' => ['1.123451', 4, '1.1234'],
            'negative_decimal_floor_2' => ['-1.123451', 4, '-1.1235'],
        ];
    }

    #[DataProvider('floorProvider')]
    public function testFloor(string $input, string $expectedResult): void
    {
        $sut = new PrecisionMath();

        $result = $sut->floor($input);

        $msg = sprintf("%s, %s -> %s - %s", 'floor', $input, $expectedResult, $result);

        static::assertTrue(bccomp($expectedResult, $result, 100) === 0, $msg);
    }

    public static function floorProvider(): array
    {
        return [
            'basic' => ['1.23', '1'],
            'basic_negative' => ['-1.23', '-2'],
            'non_decimal' => ['1', '1'],
            'negative_non_decimal' => ['-1', '-1'],
        ];
    }

    #[DataProvider('provideCeiling')]
    public function testCeiling(string $input, string $expectedResult): void
    {
        $sut = new PrecisionMath();

        $result = $sut->ceiling($input);

        $msg = sprintf("%s, %s -> %s - %s", 'ceiling', $input, $expectedResult, $result);

        static::assertTrue(bccomp($expectedResult, $result, 100) === 0, $msg);
    }

    public static function provideCeiling(): array
    {
        return [
            'basic' => ['1.23', '2'],
            'basic_negative' => ['-1.23', '-1'],
            'non_decimal' => ['-10', '-10'],
            'non_decimal_negative' => ['-10', '-10'],
        ];
    }

    #[DataProvider('provideAbs')]
    public function testAbs(string $number, string $expectedResult): void
    {
        $sut = new PrecisionMath();

        $result = $sut->abs($number);

        $msg = sprintf("%s, %s -> %s - %s", 'abs', $number, $expectedResult, $result);

        static::assertTrue(bccomp($expectedResult, $result, 100) === 0, $msg);
    }

    public static function provideAbs(): array
    {
        return [
            'basic_integer' => ['-100', '100'],
            'basic_decimal' => ['-100.2345', '100.2345'],
            'positive_integer' => ['100', '100'],
            'positive_decimal' => ['100.2345', '100.2345'],
        ];
    }

    #[DataProvider('provideMin')]
    public function testMin(array $numbers, string|throwable $expectedResult): void
    {
        $sut = new PrecisionMath();

        if ($expectedResult instanceof throwable) {
            static::expectException(get_class($expectedResult));
        }

        $result = $sut->min($numbers);

        static::assertEquals($expectedResult, $result);
    }

    public static function provideMin(): array
    {
        return [
            'basic' => [
                ['10', '2', '40'], '2'
            ],
            'negative' => [
                ['-10', '-2', '-40'], '-40'
            ],
            'decimal' => [
                ['10.23', '10.45', '10.53'], '10.23'
            ],
            'empty' => [
                [], new ValueError()
            ]
        ];
    }

    #[DataProvider('provideMax')]
    public function testMax(array $numbers, string|throwable $expectedResult): void
    {
        $sut = new PrecisionMath();

        if ($expectedResult instanceof throwable) {
            static::expectException(get_class($expectedResult));
        }

        $result = $sut->max($numbers);

        static::assertTrue(strcmp($expectedResult, $result) === 0);
    }

    public static function provideMax(): array
    {
        return [
            'basic' => [
                ['10', '2', '40'], '40',
            ],
            'negative' => [
                ['-10', '-2', '-40'], '-2',
            ],
            'decimal' => [
                ['10.23', '10.45', '10.53'], '10.53',
            ],
            'empty' => [
                [], new ValueError(),
            ]
        ];
    }

    #[DataProvider('provideSort')]
    public function testSort(array $unorderedNumbers, array $orderedNumbers, ?bool $descending = null): void
    {
        $sut = new PrecisionMath();

        $result = $sut->sort($unorderedNumbers, $descending);

        static::assertEquals($orderedNumbers, $result);
    }

    public static function provideSort(): array
    {
        return [
            'default_ascending' => [
                [
                    '123.324245',
                    '564344.45648',
                    '44.45648',
                    '-44.45648',
                    '0.45648',
                    '0.46648',
                ],
                [
                    '-44.45648',
                    '0.45648',
                    '0.46648',
                    '44.45648',
                    '123.324245',
                    '564344.45648',
                ]
            ],
            'basic_ascending' => [
                [
                    '123.324245',
                    '564344.45648',
                    '44.45648',
                    '-44.45648',
                    '0.45648',
                    '0.46648',
                ],
                [
                    '-44.45648',
                    '0.45648',
                    '0.46648',
                    '44.45648',
                    '123.324245',
                    '564344.45648',
                ],
                false,
            ],
            'basic_descending' => [
                [
                    '123.324245',
                    '564344.45648',
                    '44.45648',
                    '-44.45648',
                    '0.45648',
                    '0.46648',
                ],
                [
                    '564344.45648',
                    '123.324245',
                    '44.45648',
                    '0.46648',
                    '0.45648',
                    '-44.45648',
                ],
                true,
            ],
        ];
    }

    #[DataProvider('provideSum')]
    public function testSum(array $numbers, string|throwable $expectedResult): void
    {
        $sut = new PrecisionMath();

        if ($expectedResult instanceof throwable) {
            static::expectException(get_class($expectedResult));
        }

        $result = $sut->sum($numbers);

        static::assertTrue(strcmp($expectedResult, $result) === 0);
    }

    public static function provideSum(): array
    {
        return [
            'basic' => [
                ['10', '10', '10', '10'], '40',
            ],
            'negative' => [
                ['-10', '-10', '-10', '-10'], '-40',
            ],
            'mixed_sign' => [
                ['10', '-10', '-10', '-10'], '-20',
            ],
            'mixed_sign_decimal' => [
                ['10', '-10.235', '-10', '-10.767'], '-21.002',
            ],
            'empty' => [
                [], new ValueError(),
            ],
        ];
    }

    #[DataProvider('provideComp')]
    public function testComp(string $leftOperand, string $rightOperand, int $expectedResult): void
    {
        $sut = new PrecisionMath();

        $result = $sut->comp($leftOperand, $rightOperand, 0);

        static::assertEquals($expectedResult, $result);
    }

    public static function provideComp(): array
    {
        return [
            'less' => ['1', '2', -1],
            'equal' => ['1', '1', 0],
            'greater' => ['2', '1', 1],
        ];
    }

    #[DataProvider('provideLt')]
    public function testLt(string $leftOperand, string $rightOperand, bool $expectedResult): void
    {
        $sut = new PrecisionMath();

        $result = $sut->lt($leftOperand, $rightOperand, 0);

        static::assertEquals($expectedResult, $result);
    }

    public static function provideLt(): array
    {
        return [
            'less' => ['1', '2', true],
            'equal' => ['1', '1', false],
            'greater' => ['2', '1', false],
        ];
    }

    #[DataProvider('provideLte')]
    public function testLte(string $leftOperand, string $rightOperand, bool $expectedResult): void
    {
        $sut = new PrecisionMath();

        $result = $sut->lte($leftOperand, $rightOperand);

        static::assertEquals($expectedResult, $result);
    }

    public static function provideLte(): array
    {
        return [
            'less' => ['1', '2', true],
            'equal' => ['1', '1', true],
            'greater' => ['2', '1', false],
        ];
    }

    #[DataProvider('provideEq')]
    public function testEq(string $leftOperand, string $rightOperand, bool $expectedResult): void
    {
        $sut = new PrecisionMath();

        $result = $sut->eq($leftOperand, $rightOperand);

        static::assertEquals($expectedResult, $result);
    }

    public static function provideEq(): array
    {
        return [
            'less' => ['1', '2', false],
            'equal' => ['1', '1', true],
            'greater' => ['2', '1', false],
        ];
    }

    #[DataProvider('provideGte')]
    public function testGte(string $leftOperand, string $rightOperand, bool $expectedResult): void
    {
        $sut = new PrecisionMath();

        $result = $sut->gte($leftOperand, $rightOperand);

        static::assertEquals($expectedResult, $result);
    }

    public static function provideGte(): array
    {
        return [
            'less' => ['1', '2', false],
            'equal' => ['1', '1', true],
            'greater' => ['2', '1', true],
        ];
    }

    #[DataProvider('provideGt')]
    public function testGt(string $leftOperand, string $rightOperand, bool $expectedResult): void
    {
        $sut = new PrecisionMath();

        $result = $sut->gt($leftOperand, $rightOperand);

        static::assertEquals($expectedResult, $result);
    }

    public static function provideGt(): array
    {
        return [
            'less' => ['1', '2', false],
            'equal' => ['1', '1', false],
            'greater' => ['2', '1', true],
        ];
    }

    public function testMod(): void
    {
        $sut = new PrecisionMath();

        $leftOperand = '8';
        $rightOperand = '3';

        $expectedResult = '2';

        $result = $sut->mod($leftOperand, $rightOperand, 0);

        $msg = sprintf("mod %s %s > %s (actual: %s)", $leftOperand, $rightOperand, $expectedResult, $result);

        static::assertTrue(strcmp($expectedResult, $result) === 0, $msg);
    }

    public function testPow(): void
    {
        $sut = new PrecisionMath();

        $leftOperand = '10';
        $rightOperand = '2';

        $expectedResult = '100.00';

        $result = $sut->pow($leftOperand, $rightOperand, 2);

        $msg = sprintf("pow %s %s > %s (actual: %s)", $leftOperand, $rightOperand, $expectedResult, $result);

        static::assertTrue(strcmp($expectedResult, $result) === 0, $msg);
    }
}
