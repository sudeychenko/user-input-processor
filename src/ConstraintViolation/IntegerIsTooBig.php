<?php

declare(strict_types=1);

namespace Flaksp\UserInputProcessor\ConstraintViolation;

use Flaksp\UserInputProcessor\Pointer;

final class IntegerIsTooBig implements ConstraintViolationInterface
{
    public const TYPE = 'integer_is_too_big';

    public function __construct(
        private Pointer $pointer,
        private int $max
    ) {
    }

    public static function getType(): string
    {
        return self::TYPE;
    }

    public function getDescription(): string
    {
        return 'Property contains too big integer.';
    }

    public function getMax(): int
    {
        return $this->max;
    }

    public function getPointer(): Pointer
    {
        return $this->pointer;
    }
}
