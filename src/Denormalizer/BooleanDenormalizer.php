<?php

declare(strict_types=1);

namespace Flaksp\UserInputProcessor\Denormalizer;

use Flaksp\UserInputProcessor\ConstraintViolation\ConstraintViolationCollection;
use Flaksp\UserInputProcessor\ConstraintViolation\WrongPropertyType;
use Flaksp\UserInputProcessor\Exception\ValidationError;
use Flaksp\UserInputProcessor\AbstractPointer;

final class BooleanDenormalizer
{
    public function denormalize(
        mixed $data,
        AbstractPointer $pointer,
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
