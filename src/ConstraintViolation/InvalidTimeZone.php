<?php

declare(strict_types=1);

namespace UserInputProcessor\ConstraintViolation;

use Override;
use UserInputProcessor\Pointer;

final readonly class InvalidTimeZone implements ConstraintViolationInterface
{
    public function __construct(private Pointer $pointer, private string $description)
    {
    }

    #[Override]
    public static function getType(): string
    {
        return 'timezone_is_not_valid';
    }

    #[Override]
    public function getDescription(): string
    {
        return $this->description;
    }

    #[Override]
    public function getPointer(): Pointer
    {
        return $this->pointer;
    }
}
