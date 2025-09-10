<?php

declare(strict_types=1);

namespace UserInputProcessor\Denormalizer;

use LogicException;
use UserInputProcessor\ConstraintViolation\ConstraintViolationInterface;
use UserInputProcessor\ConstraintViolation\NumberIsTooBig;
use UserInputProcessor\ConstraintViolation\NumberIsTooSmall;
use UserInputProcessor\ConstraintViolation\WrongPropertyType;
use UserInputProcessor\Exception\ValidationError;
use UserInputProcessor\Pointer;

/**
 * Denormalizer for fields where float is expected.
 *
 * It will fail if numeric string is passed. It should be cast to float before passing to the denormalizer.
 * However, it will not fail if integer is passed.
 */
final readonly class FloatDenormalizer
{
    /**
     * Validates and denormalizes passed data.
     *
     * It expects `$data` to be float or integer type, but also accepts additional validation requirements.
     *
     * @psalm-param mixed $data Data to validate and denormalize
     * @psalm-param Pointer $pointer Pointer containing path to current field
     * @psalm-param float|null $minimum Minimum value of float or integer
     * @psalm-param float|null $maximum Maximum value of float or integer
     *
     * @throws ValidationError If `$data` does not meet the requirements of the denormalizer
     *
     * @psalm-return float The same float as the one that was passed to `$data` argument, but if
     *               integer was passed to `$data`, it will be cast to float
     */
    public function denormalize(mixed $data, Pointer $pointer, ?float $minimum = null, ?float $maximum = null): float
    {
        if (null !== $minimum && null !== $maximum && $minimum > $maximum) {
            throw new LogicException('Minimum constraint can not be bigger than maximum');
        }

        /** @psalm-var list<ConstraintViolationInterface> $violations */
        $violations = [];

        if (!\is_int($data) && !\is_float($data)) {
            $violations[] = WrongPropertyType::guessGivenType($pointer, $data, [WrongPropertyType::JSON_TYPE_FLOAT]);

            throw new ValidationError($violations);
        }

        if (null !== $minimum && $data < $minimum) {
            $violations[] = new NumberIsTooSmall($pointer, $minimum);
        }

        if (null !== $maximum && $data > $maximum) {
            $violations[] = new NumberIsTooBig($pointer, $maximum);
        }

        if (\count($violations) > 0) {
            throw new ValidationError($violations);
        }

        return (float) $data;
    }
}
