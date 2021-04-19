<?php

declare(strict_types=1);

namespace Flaksp\UserInputProcessor\ConstraintViolation;

use Flaksp\UserInputProcessor\AbstractPointer;

final class MandatoryFieldMissing implements ConstraintViolationInterface
{
    public const TYPE = 'mandatory_field_missing';

    public function __construct(
        private AbstractPointer $pointer
    ) {
    }

    public static function getType(): string
    {
        return self::TYPE;
    }

    public function getDescription(): string
    {
        return sprintf(
            'Property "%s" is mandatory, but it\'s missing. Even if field is nullable it should be presented in request payload.',
            $this->pointer->getPointer()
        );
    }

    public function getPointer(): AbstractPointer
    {
        return $this->pointer;
    }
}
