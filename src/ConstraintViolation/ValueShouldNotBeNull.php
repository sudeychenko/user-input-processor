<?php

declare(strict_types=1);

namespace Flaksp\UserInputProcessor\ConstraintViolation;

use Flaksp\UserInputProcessor\AbstractPointer;

final class ValueShouldNotBeNull implements ConstraintViolationInterface
{
    public const TYPE = 'value_should_not_be_null';

    public function __construct(
        private AbstractPointer $pointer
    ) {
    }

    public static function getType(): string
    {
        return self::TYPE;
    }

    public function getDescription(): string
    {
        return sprintf(
            'Property "%s" should not be null.',
            $this->pointer->getPointer()
        );
    }

    public function getPointer(): AbstractPointer
    {
        return $this->pointer;
    }
}
