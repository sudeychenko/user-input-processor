<?php

declare(strict_types=1);

namespace Flaksp\UserInputDeserializer\Deserializer;

use Flaksp\UserInputDeserializer\ConstraintViolation\ConstraintViolationCollection;
use Flaksp\UserInputDeserializer\ConstraintViolation\IntegerIsTooBig;
use Flaksp\UserInputDeserializer\ConstraintViolation\IntegerIsTooSmall;
use Flaksp\UserInputDeserializer\ConstraintViolation\WrongPropertyType;
use Flaksp\UserInputDeserializer\Exception\ValidationError;
use Flaksp\UserInputDeserializer\JsonPointer;
use LogicException;

final class IntegerDeserializer
{
    public function deserialize(
        $data,
        JsonPointer $pointer,
        bool $isNullable = false,
        int $minimum = null,
        int $maximum = null,
    ): ?int {
        if (null !== $minimum && null !== $maximum && $minimum > $maximum) {
            throw new LogicException('Minimum constraint can not be bigger than maximum');
        }

        if (null === $data && $isNullable) {
            return null;
        }

        $violations = new ConstraintViolationCollection();

        if (!\is_int($data)) {
            $violations[] = WrongPropertyType::guessGivenType(
                $pointer,
                $data,
                [WrongPropertyType::JSON_TYPE_INTEGER]
            );

            throw new ValidationError($violations);
        }

        if (null !== $minimum && $data < $minimum) {
            $violations[] = new IntegerIsTooSmall(
                $pointer,
                $minimum
            );
        }

        if (null !== $maximum && $data > $maximum) {
            $violations[] = new IntegerIsTooBig(
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
