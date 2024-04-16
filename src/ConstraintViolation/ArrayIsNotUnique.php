<?php

declare(strict_types=1);

namespace Spiks\UserInputProcessor\ConstraintViolation;

use Spiks\UserInputProcessor\Pointer;

class ArrayIsNotUnique implements ConstraintViolationInterface
{
    public function __construct(private readonly Pointer $pointer)
    {
    }

    public static function getType(): string
    {
        return 'array_is_not_unique';
    }

    public function getDescription(): string
    {
        return 'Array is not unique';
    }

    public function getPointer(): Pointer
    {
        return $this->pointer;
    }
}
