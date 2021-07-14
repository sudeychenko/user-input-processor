<?php

declare(strict_types=1);

namespace Flaksp\UserInputProcessor\ConstraintViolation;

use Flaksp\UserInputProcessor\Pointer;

final class NumberIsTooBig implements ConstraintViolationInterface
{
    public const TYPE = 'number_is_too_big';

    public function __construct(
        private Pointer $pointer,
        private float $max
    ) {
    }

    public static function getType(): string
    {
        return self::TYPE;
    }

    public function getDescription(): string
    {
        return 'Property contains too big number.';
    }

    public function getMax(): float
    {
        return $this->max;
    }

    public function getPointer(): Pointer
    {
        return $this->pointer;
    }
}
