<?php

declare(strict_types=1);

namespace Spiks\UserInputProcessor\ConstraintViolation;

use Spiks\UserInputProcessor\Pointer;

final class NumberIsTooSmall implements ConstraintViolationInterface
{
    public const TYPE = 'number_is_too_small';

    public function __construct(
        private Pointer $pointer,
        private float $min
    ) {
    }

    public static function getType(): string
    {
        return self::TYPE;
    }

    public function getDescription(): string
    {
        return 'Property contains too small number.';
    }

    public function getMin(): float
    {
        return $this->min;
    }

    public function getPointer(): Pointer
    {
        return $this->pointer;
    }
}
