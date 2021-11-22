<?php

declare(strict_types=1);

namespace Spiks\UserInputProcessor\ConstraintViolation;

use Spiks\UserInputProcessor\Pointer;

final class ArrayIsTooLong implements ConstraintViolationInterface
{
    public const TYPE = 'array_is_too_long';

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
        return 'Property contains too long array.';
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
