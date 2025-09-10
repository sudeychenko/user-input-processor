<?php

declare(strict_types=1);

namespace UserInputProcessor\ConstraintViolation;

use Override;
use UserInputProcessor\Pointer;

final readonly class MandatoryFieldMissing implements ConstraintViolationInterface
{
    public const string TYPE = 'mandatory_field_missing';

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
        return 'Property is mandatory, but it\'s missing. Even if field is nullable it should be presented in request payload.';
    }

    #[Override]
    public function getPointer(): Pointer
    {
        return $this->pointer;
    }
}
