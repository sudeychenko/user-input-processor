<?php

declare(strict_types=1);

namespace Flaksp\UserInputProcessor\ConstraintViolation;

use Flaksp\UserInputProcessor\AbstractPointer;

final class ValueDoesNotMatchRegex implements ConstraintViolationInterface
{
    public const TYPE = 'value_does_not_match_regex';

    public function __construct(
        private AbstractPointer $pointer,
        private string $regex
    ) {
    }

    public static function getType(): string
    {
        return self::TYPE;
    }

    public function getDescription(): string
    {
        return sprintf(
            'Property "%s" does not match regex "%s".',
            $this->pointer->getPointer(),
            $this->getRegex()
        );
    }

    public function getPointer(): AbstractPointer
    {
        return $this->pointer;
    }

    public function getRegex(): string
    {
        return $this->regex;
    }
}
