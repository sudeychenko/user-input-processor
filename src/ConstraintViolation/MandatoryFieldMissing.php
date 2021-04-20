<?php

declare(strict_types=1);

namespace Flaksp\UserInputProcessor\ConstraintViolation;

use Flaksp\UserInputProcessor\Pointer;

final class MandatoryFieldMissing implements ConstraintViolationInterface
{
    public const TYPE = 'mandatory_field_missing';

    public function __construct(
        private Pointer $pointer
    ) {
    }

    public static function getType(): string
    {
        return self::TYPE;
    }

    public function getDescription(): string
    {
        return 'Property is mandatory, but it\'s missing. Even if field is nullable it should be presented in request payload.';
    }

    public function getPointer(): Pointer
    {
        return $this->pointer;
    }
}
