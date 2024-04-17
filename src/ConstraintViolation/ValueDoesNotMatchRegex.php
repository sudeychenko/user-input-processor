<?php

declare(strict_types=1);

namespace Spiks\UserInputProcessor\ConstraintViolation;

use Spiks\UserInputProcessor\Pointer;

final class ValueDoesNotMatchRegex implements ConstraintViolationInterface
{
    public const TYPE = 'value_does_not_match_regex';

    public function __construct(private Pointer $pointer, private string $regex)
    {
    }

    public static function getType(): string
    {
        return self::TYPE;
    }

    public function getDescription(): string
    {
        return sprintf('Property does not match regex "%s".', $this->getRegex());
    }

    public function getPointer(): Pointer
    {
        return $this->pointer;
    }

    public function getRegex(): string
    {
        return $this->regex;
    }
}
