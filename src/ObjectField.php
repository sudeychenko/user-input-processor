<?php

declare(strict_types=1);

namespace UserInputProcessor;

use Closure;

/**
 * This class represents a field in an object (associative array).
 */
final readonly class ObjectField
{
    /**
     * @psalm-param Closure(mixed, Pointer): mixed $denormalizer Denormalizer function that handles denormalization of the field.
     *                                                           First parameter of the function will contain value of the field.
     *                                                           The second one will contain {@see Pointer} pointing to the field.
     * @psalm-param bool $isMandatory Should the field be presented in payload or not
     * @psalm-param bool $isNullable May the field be `null` or not
     */
    public function __construct(
        private Closure $denormalizer,
        private bool $isMandatory = true,
        private bool $isNullable = false,
    ) {
    }

    /**
     * This method returns denormalizer function that handles denormalization of the field.
     * First parameter of the function will contain value of the field.
     * The second one will contain {@see Pointer} pointing to the field.
     *
     * @psalm-return Closure(mixed, Pointer): mixed
     */
    public function getDenormalizer(): Closure
    {
        return $this->denormalizer;
    }

    /**
     * Should field be presented in payload or not.
     */
    public function isMandatory(): bool
    {
        return $this->isMandatory;
    }

    /**
     * May field be `null` or not.
     */
    public function isNullable(): bool
    {
        return $this->isNullable;
    }
}
