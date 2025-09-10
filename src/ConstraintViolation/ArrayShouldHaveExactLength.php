<?php

declare(strict_types=1);

namespace UserInputProcessor\ConstraintViolation;

use Override;
use UserInputProcessor\Pointer;

final readonly class ArrayShouldHaveExactLength implements ConstraintViolationInterface
{
    public const string TYPE = 'array_should_have_exact_length';

    /**
     * @psalm-param int<0, max> $length
     */
    public function __construct(private Pointer $pointer, private int $length)
    {
    }

    #[Override]
    public static function getType(): string
    {
        return self::TYPE;
    }

    #[Override]
    public function getDescription(): string
    {
        return \sprintf('Property should contain array with %d elements.', $this->getLength());
    }

    /**
     * @psalm-return int<0, max>
     */
    public function getLength(): int
    {
        return $this->length;
    }

    #[Override]
    public function getPointer(): Pointer
    {
        return $this->pointer;
    }
}
