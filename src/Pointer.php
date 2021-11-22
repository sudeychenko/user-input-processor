<?php

declare(strict_types=1);

namespace Flaksp\UserInputProcessor;

/**
 * This class represents path to the property.
 */
class Pointer
{
    /**
     * @param array $propertyPath Path to the property
     */
    public function __construct(
        protected array $propertyPath
    ) {
    }

    /**
     * Creates new Pointer using old one, and appending path segments to it.
     *
     * @param self       $pointer      Old pointer
     * @param string|int ...$pathItems Path segments to append to the Pointer
     *
     * @return self New instance of the Pointer
     */
    public static function append(self $pointer, string | int ...$pathItems): self
    {
        return new self(array_merge(
            $pointer->getPropertyPath(),
            $pathItems,
        ));
    }

    /**
     * Creates Pointer with empty path.
     */
    public static function empty(): self
    {
        return new self([]);
    }

    /**
     * @return array<string|int>
     */
    public function getPropertyPath(): array
    {
        return $this->propertyPath;
    }
}
