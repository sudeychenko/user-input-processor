<?php

declare(strict_types=1);

namespace Flaksp\UserInputDeserializer\ConstraintViolation;

use Flaksp\UserInputDeserializer\JsonPointer;

final class ValueDoesNotMatchRegex implements ConstraintViolationInterface
{
    public const TYPE = 'value_does_not_match_regex';

    public function __construct(
        private JsonPointer $pointer,
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

    public function getPointer(): JsonPointer
    {
        return $this->pointer;
    }

    public function getRegex(): string
    {
        return $this->regex;
    }
}
