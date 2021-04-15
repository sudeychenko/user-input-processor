<?php

declare(strict_types=1);

namespace Flaksp\UserInputDeserializer;

final class JsonPointer
{
    public function __construct(
        private array $propertyPath
    ) {
    }

    public static function append(self $pointer, string | int ...$pathItems): self
    {
        return new self(array_merge(
            $pointer->getPropertyPath(),
            array_map(static fn (string | int $pathItem) => $pathItem, $pathItems),
        ));
    }

    public static function empty(): self
    {
        return new self([]);
    }

    public function getPointer(): string
    {
        $pointer = '#';

        foreach ($this->propertyPath as $pathItem) {
            $pointer .= '/' . $pathItem;
        }

        return $pointer;
    }

    public function getPropertyPath(): array
    {
        return $this->propertyPath;
    }
}
