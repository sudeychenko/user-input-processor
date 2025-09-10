<?php

declare(strict_types=1);

namespace UserInputProcessor\ConstraintViolation;

use BackedEnum;
use Override;
use UserInputProcessor\Pointer;

final readonly class EnumValueIsNotAllowed implements ConstraintViolationInterface
{
    /**
     * @template T of BackedEnum
     *
     * @psalm-param list<T> $allowedValues
     */
    public function __construct(
        private Pointer $pointer,
        private array $allowedValues,
    ) {
    }

    #[Override]
    public static function getType(): string
    {
        return 'enum_value_is_not_allowed';
    }

    #[Override]
    public function getDescription(): string
    {
        return \sprintf(
            'Passed enumeration value is not allowed. Allowed values: %s',
            implode(', ', array_map(static fn(BackedEnum $enum): string => (string) $enum->value, $this->allowedValues)),
        );
    }

    #[Override]
    public function getPointer(): Pointer
    {
        return $this->pointer;
    }
}
