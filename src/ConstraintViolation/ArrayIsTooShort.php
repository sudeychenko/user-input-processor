<?php

declare(strict_types=1);

namespace Flaksp\UserInputDeserializer\ConstraintViolation;

use Flaksp\UserInputDeserializer\JsonPointer;

final class ArrayIsTooShort implements ConstraintViolationInterface
{
    public const TYPE = 'array_is_too_short';

    public function __construct(
        private JsonPointer $pointer,
        private int $minLength
    ) {
    }

    public static function getType(): string
    {
        return self::TYPE;
    }

    public function getDescription(): string
    {
        return sprintf(
            'Property "%s" contains too short array.',
            $this->pointer->getPointer(),
        );
    }

    public function getMinLength(): int
    {
        return $this->minLength;
    }

    public function getPointer(): JsonPointer
    {
        return $this->pointer;
    }
}
