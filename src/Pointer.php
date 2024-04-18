<?php

declare(strict_types=1);

namespace Spiks\UserInputProcessor;

/**
 * This class represents path to the property.
 */
class Pointer
{
    /**
     * @param list<string> $propertyPath Path to the property
     */
    public function __construct(protected array $propertyPath)
    {
    }

    /**
     * Creates new Pointer using old one, and appending path segments to it.
     *
     * @param self   $pointer      Old pointer
     * @param string ...$pathItems Path segments to append to the Pointer
     *
     * @return self New instance of the Pointer
     */
    public static function append(self $pointer, string ...$pathItems): self
    {
        return new self([...$pointer->getPropertyPath(), ...array_values($pathItems)]);
    }

    /**
     * Creates Pointer with empty path.
     */
    public static function empty(): self
    {
        return new self([]);
    }

    /**
     * @return list<string>
     */
    public function getPropertyPath(): array
    {
        return $this->propertyPath;
    }

    /**
     * Converts pointer to string.
     *
     * @internal Should not be used outside the library
     */
    public function toString(): string
    {
        return '/' . implode('/', $this->getPropertyPath());
    }
}
