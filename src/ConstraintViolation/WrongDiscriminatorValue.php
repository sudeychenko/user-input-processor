<?php

declare(strict_types=1);

namespace Spiks\UserInputProcessor\ConstraintViolation;

use Spiks\UserInputProcessor\Pointer;

final class WrongDiscriminatorValue implements ConstraintViolationInterface
{
    public const TYPE = 'wrong_discriminator_value';

    /**
     * @param string[] $possibleValues
     */
    public function __construct(
        private Pointer $pointer,
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
            'Property contains invalid discriminator value, possible values are: %s.',
            implode(', ', $this->possibleValues),
        );
    }

    public function getPointer(): Pointer
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
