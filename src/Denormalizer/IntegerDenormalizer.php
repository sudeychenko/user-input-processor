<?php

declare(strict_types=1);

namespace Spiks\UserInputProcessor\Denormalizer;

use LogicException;
use Spiks\UserInputProcessor\ConstraintViolation\ConstraintViolationInterface;
use Spiks\UserInputProcessor\ConstraintViolation\NumberIsTooBig;
use Spiks\UserInputProcessor\ConstraintViolation\NumberIsTooSmall;
use Spiks\UserInputProcessor\ConstraintViolation\WrongPropertyType;
use Spiks\UserInputProcessor\Exception\ValidationError;
use Spiks\UserInputProcessor\Pointer;

/**
 * Denormalizer for fields where integer is expected.
 *
 * It will fail if numeric string or float is passed. It should be cast to integer before passing to the denormalizer.
 */
final class IntegerDenormalizer
{
    /**
     * Validates and denormalizes passed data.
     *
     * It expects `$data` to be integer type, but also accepts additional validation requirements.
     *
     * @param mixed    $data    Data to validate and denormalize
     * @param Pointer  $pointer Pointer containing path to current field
     * @param int|null $minimum Minimum value of integer
     * @param int|null $maximum Maximum value of integer
     *
     * @throws ValidationError If `$data` does not meet the requirements of the denormalizer
     *
     * @return int The same integer as the one that was passed to `$data` argument
     */
    public function denormalize(
        mixed $data,
        Pointer $pointer,
        int $minimum = null,
        int $maximum = null,
    ): int {
        if (null !== $minimum && null !== $maximum && $minimum > $maximum) {
            throw new LogicException('Minimum constraint can not be bigger than maximum');
        }

        /** @var list<ConstraintViolationInterface> $violations */
        $violations = [];

        if (!\is_int($data)) {
            $violations[] = WrongPropertyType::guessGivenType(
                $pointer,
                $data,
                [WrongPropertyType::JSON_TYPE_NUMBER]
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

        if (\count($violations) > 0) {
            throw new ValidationError($violations);
        }

        return $data;
    }
}
