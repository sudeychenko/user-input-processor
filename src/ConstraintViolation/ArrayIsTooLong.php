<?php

declare(strict_types=1);

namespace Flaksp\UserInputProcessor\ConstraintViolation;

use Flaksp\UserInputProcessor\AbstractPointer;

final class ArrayIsTooLong implements ConstraintViolationInterface
{
    public const TYPE = 'array_is_too_long';

    public function __construct(
        private AbstractPointer $pointer,
        private int $maxLength
    ) {
    }

    public static function getType(): string
    {
        return self::TYPE;
    }

    public function getDescription(): string
    {
        return sprintf(
            'Property "%s" contains too long array.',
            $this->pointer->getPointer(),
        );
    }

    public function getMaxLength(): int
    {
        return $this->maxLength;
    }

    public function getPointer(): AbstractPointer
    {
        return $this->pointer;
    }
}
