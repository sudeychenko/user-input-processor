<?php

declare(strict_types=1);

namespace Flaksp\UserInputProcessor\ConstraintViolation;

use Flaksp\UserInputProcessor\Pointer;

final class StringIsTooShort implements ConstraintViolationInterface
{
    public const TYPE = 'string_is_too_short';

    public function __construct(
        private Pointer $pointer,
        private int $minLength
    ) {
    }

    public static function getType(): string
    {
        return self::TYPE;
    }

    public function getDescription(): string
    {
        return 'Property contains too short string.';
    }

    public function getMinLength(): int
    {
        return $this->minLength;
    }

    public function getPointer(): Pointer
    {
        return $this->pointer;
    }
}
