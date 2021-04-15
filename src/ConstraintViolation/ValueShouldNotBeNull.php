<?php

declare(strict_types=1);

namespace Flaksp\UserInputDeserializer\ConstraintViolation;

use Flaksp\UserInputDeserializer\JsonPointer;

final class ValueShouldNotBeNull implements ConstraintViolationInterface
{
    public const TYPE = 'value_should_not_be_null';

    public function __construct(
        private JsonPointer $pointer
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

    public function getPointer(): JsonPointer
    {
        return $this->pointer;
    }
}
