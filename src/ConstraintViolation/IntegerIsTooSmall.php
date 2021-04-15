<?php

declare(strict_types=1);

namespace Flaksp\UserInputDeserializer\ConstraintViolation;

use Flaksp\UserInputDeserializer\JsonPointer;

final class IntegerIsTooSmall implements ConstraintViolationInterface
{
    public const TYPE = 'integer_is_too_small';

    public function __construct(
        private JsonPointer $pointer,
        private int $min
    ) {
    }

    public static function getType(): string
    {
        return self::TYPE;
    }

    public function getDescription(): string
    {
        return sprintf(
            'Property "%s" contains too small integer.',
            $this->pointer->getPointer(),
        );
    }

    public function getMin(): int
    {
        return $this->min;
    }

    public function getPointer(): JsonPointer
    {
        return $this->pointer;
    }
}
