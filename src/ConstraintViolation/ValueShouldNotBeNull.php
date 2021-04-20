<?php

declare(strict_types=1);

namespace Flaksp\UserInputProcessor\ConstraintViolation;

use Flaksp\UserInputProcessor\Pointer;

final class ValueShouldNotBeNull implements ConstraintViolationInterface
{
    public const TYPE = 'value_should_not_be_null';

    public function __construct(
        private Pointer $pointer
    ) {
    }

    public static function getType(): string
    {
        return self::TYPE;
    }

    public function getDescription(): string
    {
        return 'Property should not be null.';
    }

    public function getPointer(): Pointer
    {
        return $this->pointer;
    }
}
