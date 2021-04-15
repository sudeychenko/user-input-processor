<?php

declare(strict_types=1);

namespace Flaksp\UserInputDeserializer\Deserializer;

use Flaksp\UserInputDeserializer\ConstraintViolation\ConstraintViolationCollection;
use Flaksp\UserInputDeserializer\ConstraintViolation\StringIsTooLong;
use Flaksp\UserInputDeserializer\ConstraintViolation\StringIsTooShort;
use Flaksp\UserInputDeserializer\ConstraintViolation\ValueDoesNotMatchRegex;
use Flaksp\UserInputDeserializer\ConstraintViolation\WrongPropertyType;
use Flaksp\UserInputDeserializer\Exception\ValidationError;
use Flaksp\UserInputDeserializer\JsonPointer;
use LogicException;

final class StringDeserializer
{
    public function deserialize(
        $data,
        JsonPointer $pointer,
        bool $isNullable = false,
        int $minLength = null,
        int $maxLength = null,
        string $pattern = null,
    ): ?string {
        if (null !== $minLength && null !== $maxLength && $minLength > $maxLength) {
            throw new LogicException('Min length constraint can not be bigger than max length');
        }

        if (null === $data && $isNullable) {
            return null;
        }

        $violations = new ConstraintViolationCollection();

        if (!\is_string($data)) {
            $violations[] = WrongPropertyType::guessGivenType(
                $pointer,
                $data,
                [WrongPropertyType::JSON_TYPE_STRING]
            );

            throw new ValidationError($violations);
        }

        if (null !== $minLength && mb_strlen($data) < $minLength) {
            $violations[] = new StringIsTooShort(
                $pointer,
                $minLength
            );
        }

        if (null !== $maxLength && mb_strlen($data) > $maxLength) {
            $violations[] = new StringIsTooLong(
                $pointer,
                $maxLength
            );
        }

        if (null !== $pattern && 1 !== preg_match($pattern, $data)) {
            $violations[] = new ValueDoesNotMatchRegex(
                $pointer,
                $pattern
            );
        }

        if (\count($violations) > 0) {
            throw new ValidationError($violations);
        }

        return $data;
    }
}
