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
 * Denormalizer for fields where integer is expected.
 *
 * It will fail if numeric string or float is passed. It should be cast to integer before passing to the denormalizer.
 */
final readonly class IntegerDenormalizer
{
    /**
     * Validates and denormalizes passed data.
     *
     * It expects `$data` to be integer type, but also accepts additional validation requirements.
     *
     * @psalm-param mixed $data Data to validate and denormalize
     * @psalm-param Pointer $pointer Pointer containing path to current field
     * @psalm-param int|null $minimum Minimum value of integer
     * @psalm-param int|null $maximum Maximum value of integer
     *
     * @throws ValidationError If `$data` does not meet the requirements of the denormalizer
     *
     * @psalm-return int The same integer as the one that was passed to `$data` argument
     */
    public function denormalize(mixed $data, Pointer $pointer, ?int $minimum = null, ?int $maximum = null): int
    {
        if (null !== $minimum && null !== $maximum && $minimum > $maximum) {
            throw new LogicException('Minimum constraint can not be bigger than maximum');
        }

        /** @psalm-var list<ConstraintViolationInterface> $violations */
        $violations = [];

        if (!\is_int($data)) {
            $violations[] = WrongPropertyType::guessGivenType($pointer, $data, [WrongPropertyType::JSON_TYPE_NUMBER]);

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

        return $data;
    }
}
