<?php

declare(strict_types=1);

namespace Spiks\UserInputProcessor\ConstraintViolation;

use Spiks\UserInputProcessor\Pointer;

class InvalidDate implements ConstraintViolationInterface
{
    public function __construct(private readonly Pointer $pointer, private readonly string $description)
    {
    }

    public static function getType(): string
    {
        return 'date_is_not_valid';
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getPointer(): Pointer
    {
        return $this->pointer;
    }
}
