<?php

declare(strict_types=1);

namespace Flaksp\UserInputProcessor\Denormalizer;

use Flaksp\UserInputProcessor\ConstraintViolation\ConstraintViolationCollection;
use Flaksp\UserInputProcessor\ConstraintViolation\NumberIsTooBig;
use Flaksp\UserInputProcessor\ConstraintViolation\NumberIsTooSmall;
use Flaksp\UserInputProcessor\ConstraintViolation\WrongPropertyType;
use Flaksp\UserInputProcessor\Exception\ValidationError;
use Flaksp\UserInputProcessor\Pointer;
use LogicException;

/**
 * Denormalizer for fields where float is expected.
 *
 * It will fail if numeric string is passed. It should be cast to float before passing to the denormalizer.
 * However, it will not fail if integer is passed.
 */
final class FloatDenormalizer
{
    /**
     * Validates and denormalizes passed data.
     *
     * It expects `$data` to be float or integer type, but also accepts additional validation requirements.
     *
     * @param mixed      $data    Data to validate and denormalize
     * @param Pointer    $pointer Pointer containing path to current field
     * @param float|null $minimum Minimum value of float or integer
     * @param float|null $maximum Maximum value of float or integer
     *
     * @throws ValidationError If `$data` does not meet the requirements of the denormalizer
     *
     * @return float The same float as the one that was passed to `$data` argument, but if
     *               integer was passed to `$data`, it will be cast to float
     */
    public function denormalize(
        mixed $data,
        Pointer $pointer,
        float $minimum = null,
        float $maximum = null,
    ): float {
        if (null !== $minimum && null !== $maximum && $minimum > $maximum) {
            throw new LogicException('Minimum constraint can not be bigger than maximum');
        }

        $violations = new ConstraintViolationCollection();

        if (!\is_int($data) && !\is_float($data)) {
            $violations[] = WrongPropertyType::guessGivenType(
                $pointer,
                $data,
                [WrongPropertyType::JSON_TYPE_FLOAT]
            );

            throw new ValidationError($violations);
        }

        if (null !== $minimum && $data < $minimum) {
            $violations[] = new NumberIsTooSmall(
                $pointer,
                $minimum
            );
        }

        if (null !== $maximum && $data > $maximum) {
            $violations[] = new NumberIsTooBig(
                $pointer,
                $maximum
            );
        }

        if ($violations->isNotEmpty()) {
            throw new ValidationError($violations);
        }

        return (float) $data;
    }
}
