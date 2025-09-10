<?php

declare(strict_types=1);

namespace UserInputProcessor\ConstraintViolation;

use Override;
use UserInputProcessor\Pointer;

final readonly class ValueShouldNotBeNull implements ConstraintViolationInterface
{
    public const string TYPE = 'value_should_not_be_null';

    public function __construct(private Pointer $pointer)
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
        return 'Property should not be null.';
    }

    #[Override]
    public function getPointer(): Pointer
    {
        return $this->pointer;
    }
}
