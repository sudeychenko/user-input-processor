<?php

declare(strict_types=1);

namespace Flaksp\UserInputProcessor\ConstraintViolation;

use Flaksp\UserInputProcessor\Pointer;

interface ConstraintViolationInterface
{
    public static function getType(): string;

    public function getDescription(): string;

    public function getPointer(): Pointer;
}
