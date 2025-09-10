<?php

declare(strict_types=1);

namespace UserInputProcessor\ConstraintViolation;

use Override;
use UserInputProcessor\Pointer;

final readonly class NumberIsTooBig implements ConstraintViolationInterface
{
    public const string TYPE = 'number_is_too_big';

    public function __construct(private Pointer $pointer, private float $max)
    {
    }

    #[Override]
    public static function getType(): string
    {
        return self::TYPE;
    }

    #[Override]
    public function getDescription(): string
    {
        return 'Property contains too big number.';
    }

    public function getMax(): float
    {
        return $this->max;
    }

    #[Override]
    public function getPointer(): Pointer
    {
        return $this->pointer;
    }
}
