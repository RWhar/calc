# Usage

The following methods are provided:

Decorators
* add(leftOperand: string, rightOperand: string, [scale: int|null = self::INTERNAL_PRECI...]): string
* sub(leftOperand: string, rightOperand: string, [scale: int|null = self::INTERNAL_PRECI...]): string
* mul(leftOperand: string, rightOperand: string, [scale: int|null = self::INTERNAL_PRECI...]): string
* div(leftOperand: string, rightOperand: string, [scale: int|null = self::INTERNAL_PRECI...]): string
* mod(leftOperand: string, rightOperand: string, [scale: int|null = self::INTERNAL_PRECI...]): string
* pow(base: string, exponent: string, [scale: int|null = self::INTERNAL_PRECI...]): string
* comp(leftOperand: string, rightOperand: string, [scale: int|null = self::INTERNAL_PRECI...]): int

Convenience
* lt(leftOperand: string, rightOperand: string, [scale: int|null = self::INTERNAL_PRECI...]): bool
* eq(leftOperand: string, rightOperand: string, [scale: int|null = self::INTERNAL_PRECI...]): bool
* gt(leftOperand: string, rightOperand: string, [scale: int|null = self::INTERNAL_PRECI...]): bool
* lte(leftOperand: string, rightOperand: string, [scale: int|null = self::INTERNAL_PRECI...]): bool
* gte(leftOperand: string, rightOperand: string, [scale: int|null = self::INTERNAL_PRECI...]): bool
* isNegative(number: string, [scale: int|null = self::INTERNAL_PRECI...]): bool
* hasDecimal(number: string, [scale: int|null = self::INTERNAL_PRECI...]): bool

Precision Reduction
* round(number: string, scale: int, [roundingStrategy: int = PHP_ROUND_HALF_UP]): string
* roundHalfUp(number: string, scale: int): string
* roundFloor(number: string, scale: int): string
* floor(number: string): string
* ceiling(number: string): string
* abs(number: string): string

Aggregation
* sort(numbers: string[], [descending: bool|null = false]): string[]
* min(numbers: array): string
* max(numbers: array): string
* sum(numbers: array): string
 
Formatting
* floatingDecimalPrecision(number: string): string

# Development

__Initialise__
```sh
docker compose up -d
```

Builds alpine based containers.
Creates/Updates vendor libs.

__Run Tests__
```sh
docker compose run --rm composer phpunit
```


