<?php

declare(strict_types=1);

namespace Flaksp\UserInputProcessor\ConstraintViolation;

use Flaksp\UserInputProcessor\Pointer;

final class StringIsTooLong implements ConstraintViolationInterface
{
    public const TYPE = 'string_is_too_long';

    public function __construct(
        private Pointer $pointer,
        private int $maxLength
    ) {
    }

    public static function getType(): string
    {
        return self::TYPE;
    }

    public function getDescription(): string
    {
        return 'Property contains too long string.';
    }

    public function getMaxLength(): int
    {
        return $this->maxLength;
    }

    public function getPointer(): Pointer
    {
        return $this->pointer;
    }
}
