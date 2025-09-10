<?php

declare(strict_types=1);

namespace UserInputProcessor\ConstraintViolation;

use Override;
use UserInputProcessor\Pointer;

final readonly class NumberIsTooSmall implements ConstraintViolationInterface
{
    public const string TYPE = 'number_is_too_small';

    public function __construct(private Pointer $pointer, private float $min)
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
        return 'Property contains too small number.';
    }

    public function getMin(): float
    {
        return $this->min;
    }

    #[Override]
    public function getPointer(): Pointer
    {
        return $this->pointer;
    }
}
