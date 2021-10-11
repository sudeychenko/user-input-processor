<?php

declare(strict_types=1);

namespace Flaksp\UserInputProcessor;

class Pointer
{
    public function __construct(
        protected array $propertyPath
    ) {
    }

    public static function append(self $pointer, string | int ...$pathItems): self
    {
        return new self(array_merge(
            $pointer->getPropertyPath(),
            $pathItems,
        ));
    }

    public static function empty(): self
    {
        return new self([]);
    }

    public function getPropertyPath(): array
    {
        return $this->propertyPath;
    }
}
