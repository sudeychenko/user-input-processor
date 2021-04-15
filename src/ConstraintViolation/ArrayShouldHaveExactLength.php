<?php

declare(strict_types=1);

namespace Flaksp\UserInputDeserializer\ConstraintViolation;

use Flaksp\UserInputDeserializer\JsonPointer;

final class ArrayShouldHaveExactLength implements ConstraintViolationInterface
{
    public const TYPE = 'array_should_have_exact_length';

    public function __construct(
        private JsonPointer $pointer,
        private int $length
    ) {
    }

    public static function getType(): string
    {
        return self::TYPE;
    }

    public function getDescription(): string
    {
        return sprintf(
            'Property "%s" should contain array with %d elements.',
            $this->pointer->getPointer(),
            $this->getLength()
        );
    }

    public function getLength(): int
    {
        return $this->length;
    }

    public function getPointer(): JsonPointer
    {
        return $this->pointer;
    }
}
