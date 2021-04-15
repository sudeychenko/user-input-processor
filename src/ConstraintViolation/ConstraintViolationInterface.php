<?php

declare(strict_types=1);

namespace Flaksp\UserInputDeserializer\ConstraintViolation;

use Flaksp\UserInputDeserializer\JsonPointer;

interface ConstraintViolationInterface
{
    public static function getType(): string;

    public function getDescription(): string;

    public function getPointer(): JsonPointer;
}
