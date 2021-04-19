<?php


namespace Flaksp\UserInputProcessor;


abstract class AbstractPointer
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

    abstract public function getPointer(): string;

    public function getPropertyPath(): array
    {
        return $this->propertyPath;
    }
}
