<?php

declare(strict_types=1);

namespace UserInputProcessor\ConstraintViolation;

use Override;
use UserInputProcessor\Pointer;

final readonly class ValueDoesNotMatchRegex implements ConstraintViolationInterface
{
    public const string TYPE = 'value_does_not_match_regex';

    public function __construct(private Pointer $pointer, private string $regex)
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
        return \sprintf('Property does not match regex "%s".', $this->getRegex());
    }

    #[Override]
    public function getPointer(): Pointer
    {
        return $this->pointer;
    }

    public function getRegex(): string
    {
        return $this->regex;
    }
}
