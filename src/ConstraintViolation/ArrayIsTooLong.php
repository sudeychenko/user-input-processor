<?php

declare(strict_types=1);

namespace UserInputProcessor\ConstraintViolation;

use Override;
use UserInputProcessor\Pointer;

final readonly class ArrayIsTooLong implements ConstraintViolationInterface
{
    public const string TYPE = 'array_is_too_long';

    /**
     * @psalm-param int<0, max> $maxLength
     */
    public function __construct(private Pointer $pointer, private int $maxLength)
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
        return 'Property contains too long array.';
    }

    /**
     * @psalm-return int<0, max>
     */
    public function getMaxLength(): int
    {
        return $this->maxLength;
    }

    #[Override]
    public function getPointer(): Pointer
    {
        return $this->pointer;
    }
}
