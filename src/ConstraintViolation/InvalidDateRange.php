<?php

declare(strict_types=1);

namespace Spiks\UserInputProcessor\ConstraintViolation;

use Spiks\UserInputProcessor\Pointer;

class InvalidDateRange implements ConstraintViolationInterface
{
    public function __construct(private readonly Pointer $pointer)
    {
    }

    public static function getType(): string
    {
        return 'date_range_is_not_valid';
    }

    public function getDescription(): string
    {
        return 'Date range is not valid.';
    }

    public function getPointer(): Pointer
    {
        return $this->pointer;
    }
}
