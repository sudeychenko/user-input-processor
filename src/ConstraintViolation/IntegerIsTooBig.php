<?php

declare(strict_types=1);

namespace Flaksp\UserInputProcessor\ConstraintViolation;

use Flaksp\UserInputProcessor\AbstractPointer;

final class IntegerIsTooBig implements ConstraintViolationInterface
{
    public const TYPE = 'integer_is_too_big';

    public function __construct(
        private AbstractPointer $pointer,
        private int $max
    ) {
    }

    public static function getType(): string
    {
        return self::TYPE;
    }

    public function getDescription(): string
    {
        return sprintf(
            'Property "%s" contains too big integer.',
            $this->pointer->getPointer(),
        );
    }

    public function getMax(): int
    {
        return $this->max;
    }

    public function getPointer(): AbstractPointer
    {
        return $this->pointer;
    }
}
