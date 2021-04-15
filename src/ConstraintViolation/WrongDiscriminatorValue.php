<?php

declare(strict_types=1);

namespace Flaksp\UserInputDeserializer\ConstraintViolation;

use Flaksp\UserInputDeserializer\JsonPointer;

final class WrongDiscriminatorValue implements ConstraintViolationInterface
{
    public const TYPE = 'wrong_discriminator_value';

    /**
     * @param string[] $possibleValues
     */
    public function __construct(
        private JsonPointer $pointer,
        private array $possibleValues
    ) {
    }

    public static function getType(): string
    {
        return self::TYPE;
    }

    public function getDescription(): string
    {
        return sprintf(
            'Property "%s" contains invalid discriminator value, possible values are: %s.',
            $this->pointer->getPointer(),
            implode(', ', $this->possibleValues),
        );
    }

    public function getPointer(): JsonPointer
    {
        return $this->pointer;
    }

    /**
     * @return string[]
     */
    public function getPossibleValues(): array
    {
        return $this->possibleValues;
    }
}
