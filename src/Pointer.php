<?php

declare(strict_types=1);

namespace Flaksp\UserInputProcessor;

class Pointer
{
    public function __construct(
        protected array $propertyPath
    ) {
    }

    public static function append(self $pointer, string | int ...$pathItems): static
    {
        return new static(array_merge(
            $pointer->getPropertyPath(),
            array_map(static fn (string | int $pathItem) => $pathItem, $pathItems),
        ));
    }

    public static function empty(): static
    {
        return new static([]);
    }

    public function getPropertyPath(): array
    {
        return $this->propertyPath;
    }
}
