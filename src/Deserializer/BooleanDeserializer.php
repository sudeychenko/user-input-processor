<?php

declare(strict_types=1);

namespace Flaksp\UserInputDeserializer\Deserializer;

use Flaksp\UserInputDeserializer\ConstraintViolation\ConstraintViolationCollection;
use Flaksp\UserInputDeserializer\ConstraintViolation\WrongPropertyType;
use Flaksp\UserInputDeserializer\Exception\ValidationError;
use Flaksp\UserInputDeserializer\JsonPointer;

final class BooleanDeserializer
{
    public function deserialize(
        $data,
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
