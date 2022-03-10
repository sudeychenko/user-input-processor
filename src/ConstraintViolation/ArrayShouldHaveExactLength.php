<?php

declare(strict_types=1);

namespace Spiks\UserInputProcessor\ConstraintViolation;

use Spiks\UserInputProcessor\Pointer;

final class ArrayShouldHaveExactLength implements ConstraintViolationInterface
{
    public const TYPE = 'array_should_have_exact_length';

    /**
     * @param int<0, max> $length
     */
    public function __construct(
        private Pointer $pointer,
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
            'Property should contain array with %d elements.',
            $this->getLength()
        );
    }

    /**
     * @return int<0, max>
     */
    public function getLength(): int
    {
        return $this->length;
    }

    public function getPointer(): Pointer
    {
        return $this->pointer;
    }
}
