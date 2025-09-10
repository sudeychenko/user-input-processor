<?php

declare(strict_types=1);

namespace UserInputProcessor;

/**
 * This class represents path to the property.
 */
final readonly class Pointer
{
    /**
     * @psalm-param list<string> $propertyPath Path to the property
     */
    public function __construct(private array $propertyPath)
    {
    }

    /**
     * Creates new Pointer using old one, and appending path segments to it.
     *
     * @psalm-param self $pointer Old pointer
     * @psalm-param string ...$pathItems Path segments to append to the Pointer
     *
     * @psalm-return self New instance of the Pointer
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
     * @psalm-return list<string>
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
