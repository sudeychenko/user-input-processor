<?php

declare(strict_types=1);

namespace Spiks\UserInputProcessor\ConstraintViolation;

use BackedEnum;
use Spiks\UserInputProcessor\Pointer;

class EnumValueIsNotAllowed implements ConstraintViolationInterface
{
    /**
     * @template T of BackedEnum
     *
     * @psalm-param list<T> $allowedValues
     */
    public function __construct(private readonly Pointer $pointer, private readonly array $allowedValues)
    {
    }

    public static function getType(): string
    {
        return 'enum_value_is_not_allowed';
    }

    public function getDescription(): string
    {
        return sprintf(
            'Passed enumeration value is not allowed. Allowed values: %s',
            implode(', ', array_map(static fn(BackedEnum $enum): string => (string) $enum->value, $this->allowedValues))
        );
    }

    public function getPointer(): Pointer
    {
        return $this->pointer;
    }
}
