<?php

declare(strict_types=1);

namespace Flaksp\UserInputProcessor\ConstraintViolation;

use Flaksp\UserInputProcessor\AbstractPointer;

final class IntegerIsTooSmall implements ConstraintViolationInterface
{
    public const TYPE = 'integer_is_too_small';

    public function __construct(
        private AbstractPointer $pointer,
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

    public function getPointer(): AbstractPointer
    {
        return $this->pointer;
    }
}
