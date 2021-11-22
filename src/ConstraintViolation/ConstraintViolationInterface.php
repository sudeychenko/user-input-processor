<?php

declare(strict_types=1);

namespace Flaksp\UserInputProcessor\ConstraintViolation;

use Flaksp\UserInputProcessor\Pointer;

interface ConstraintViolationInterface
{
    /**
     * This method returns constraint violation type. This is an identifier of constraint violation.
     */
    public static function getType(): string;

    /**
     * This method returns an explanation of constraint violation. The target audience of this description is developers
     * who will debug their code when constraint violation will be returned (in logs, API responses, errors, etc.).
     */
    public function getDescription(): string;

    /**
     * This method returns pointer that will point to the field with invalid value.
     */
    public function getPointer(): Pointer;
}
