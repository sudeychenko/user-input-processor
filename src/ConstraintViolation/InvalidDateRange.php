<?php

declare(strict_types=1);

namespace UserInputProcessor\ConstraintViolation;

use Override;
use UserInputProcessor\Pointer;

final readonly class InvalidDateRange implements ConstraintViolationInterface
{
    public function __construct(private Pointer $pointer)
    {
    }

    #[Override]
    public static function getType(): string
    {
        return 'date_range_is_not_valid';
    }

    #[Override]
    public function getDescription(): string
    {
        return 'Date range is not valid.';
    }

    #[Override]
    public function getPointer(): Pointer
    {
        return $this->pointer;
    }
}
