<?php

declare(strict_types=1);

namespace UserInputProcessor\Denormalizer;

use UserInputProcessor\ConstraintViolation\ConstraintViolationInterface;
use UserInputProcessor\ConstraintViolation\WrongPropertyType;
use UserInputProcessor\Exception\ValidationError;
use UserInputProcessor\Pointer;

/**
 * Denormalizer for fields where boolean is expected.
 */
final class BooleanDenormalizer
{
    /**
     * Validates and denormalizes passed data.
     *
     * It expects `$data` to be boolean type.
     *
     * @psalm-param mixed $data Data to validate and denormalize
     * @psalm-param Pointer $pointer Pointer containing path to current field
     *
     * @psalm-return bool The same boolean as the one that was passed to `$data` argument
     *
     * @throws ValidationError If `$data` does not meet the requirements of the denormalizer
     */
    public function denormalize(mixed $data, Pointer $pointer): bool
    {
        /** @psalm-var list<ConstraintViolationInterface> $violations */
        $violations = [];

        if (!\is_bool($data)) {
            $violations[] = WrongPropertyType::guessGivenType($pointer, $data, [WrongPropertyType::JSON_TYPE_BOOLEAN]);

            throw new ValidationError($violations);
        }

        return $data;
    }
}
