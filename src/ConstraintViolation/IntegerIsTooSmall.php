<?php

declare(strict_types=1);

namespace Flaksp\UserInputProcessor\ConstraintViolation;

use Flaksp\UserInputProcessor\Pointer;

final class IntegerIsTooSmall implements ConstraintViolationInterface
{
    public const TYPE = 'integer_is_too_small';

    public function __construct(
        private Pointer $pointer,
        private int $min
    ) {
    }

    public static function getType(): string
    {
        return self::TYPE;
    }

    public function getDescription(): string
    {
        return 'Property contains too small integer.';
    }

    public function getMin(): int
    {
        return $this->min;
    }

    public function getPointer(): Pointer
    {
        return $this->pointer;
    }
}
