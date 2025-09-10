<?php

declare(strict_types=1);

namespace UserInputProcessor\ConstraintViolation;

use Override;
use UserInputProcessor\Pointer;

final readonly class ArrayIsTooShort implements ConstraintViolationInterface
{
    public const string TYPE = 'array_is_too_short';

    /**
     * @psalm-param int<0, max> $minLength
     */
    public function __construct(private Pointer $pointer, private int $minLength)
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
        return 'Property contains too short array.';
    }

    /**
     * @psalm-return int<0, max>
     */
    public function getMinLength(): int
    {
        return $this->minLength;
    }

    #[Override]
    public function getPointer(): Pointer
    {
        return $this->pointer;
    }
}
