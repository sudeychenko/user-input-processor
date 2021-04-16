<?php

declare(strict_types=1);

namespace Flaksp\UserInputProcessor\Deserializer;

use Flaksp\UserInputProcessor\ConstraintViolation\ConstraintViolationCollection;
use Flaksp\UserInputProcessor\ConstraintViolation\WrongPropertyType;
use Flaksp\UserInputProcessor\Exception\ValidationError;
use Flaksp\UserInputProcessor\JsonPointer;

final class BooleanDeserializer
{
    public function deserialize(
        mixed $data,
        JsonPointer $pointer,
        bool $isNullable = false,
    ): ?bool {
        if (null === $data && $isNullable) {
            return null;
        }

        $violations = new ConstraintViolationCollection();

        if (!\is_bool($data)) {
            $violations[] = WrongPropertyType::guessGivenType(
                $pointer,
                $data,
                [WrongPropertyType::JSON_TYPE_BOOLEAN]
            );

            throw new ValidationError($violations);
        }

        return $data;
    }
}
