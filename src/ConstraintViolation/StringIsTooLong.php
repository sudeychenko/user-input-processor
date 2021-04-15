<?php

declare(strict_types=1);

namespace Flaksp\UserInputDeserializer\ConstraintViolation;

use Flaksp\UserInputDeserializer\JsonPointer;

final class StringIsTooLong implements ConstraintViolationInterface
{
    public const TYPE = 'string_is_too_long';

    public function __construct(
        private JsonPointer $pointer,
        private int $maxLength
    ) {
    }

    public static function getType(): string
    {
        return self::TYPE;
    }

    public function getDescription(): string
    {
        return sprintf(
            'Property "%s" contains too long string.',
            $this->pointer->getPointer(),
        );
    }

    public function getMaxLength(): int
    {
        return $this->maxLength;
    }

    public function getPointer(): JsonPointer
    {
        return $this->pointer;
    }
}
