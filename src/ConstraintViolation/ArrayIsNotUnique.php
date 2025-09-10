<?php

declare(strict_types=1);

namespace UserInputProcessor\ConstraintViolation;

use Override;
use UserInputProcessor\Pointer;

final readonly class ArrayIsNotUnique implements ConstraintViolationInterface
{
    public function __construct(private Pointer $pointer)
    {
    }

    #[Override]
    public static function getType(): string
    {
        return 'array_is_not_unique';
    }

    #[Override]
    public function getDescription(): string
    {
        return 'Array is not unique';
    }

    #[Override]
    public function getPointer(): Pointer
    {
        return $this->pointer;
    }
}
