<?php

declare(strict_types=1);

namespace Spiks\UserInputProcessor\ConstraintViolation;

use Spiks\UserInputProcessor\Pointer;
use UnexpectedValueException;

final class WrongPropertyType implements ConstraintViolationInterface
{
    public const JSON_TYPE_ARRAY = 'array';
    public const JSON_TYPE_BOOLEAN = 'boolean';
    public const JSON_TYPE_FLOAT = 'float';
    public const JSON_TYPE_NULL = 'null';
    public const JSON_TYPE_NUMBER = 'number';
    public const JSON_TYPE_OBJECT = 'object';
    public const JSON_TYPE_STRING = 'string';

    public const TYPE = 'wrong_property_type';

    public function __construct(
        private Pointer $pointer,
        private string $givenType,
        private array $allowedTypes,
    ) {
    }

    public static function getType(): string
    {
        return self::TYPE;
    }

    public static function guessGivenType(
        Pointer $pointer,
        mixed $givenValue,
        array $allowedTypes,
    ): self {
        return new self(
            $pointer,
            self::getJsonTypeFromValue($givenValue),
            $allowedTypes
        );
    }

    /**
     * @return string[]
     */
    public function getAllowedTypes(): array
    {
        return $this->allowedTypes;
    }

    public function getDescription(): string
    {
        return sprintf(
            'Property is %s type, but only following types are allowed: %s',
            $this->givenType,
            implode(', ', $this->allowedTypes)
        );
    }

    public function getGivenType(): string
    {
        return $this->givenType;
    }

    public function getPointer(): Pointer
    {
        return $this->pointer;
    }

    private static function getJsonTypeFromValue(mixed $value): string
    {
        if (\is_array($value)) {
            return array_is_list($value)
                ? self::JSON_TYPE_ARRAY
                : self::JSON_TYPE_OBJECT;
        }

        $type = \gettype($value);

        return match ($type) {
            'boolean' => self::JSON_TYPE_BOOLEAN,
            'integer' => self::JSON_TYPE_NUMBER,
            'double' => self::JSON_TYPE_FLOAT,
            'string' => self::JSON_TYPE_STRING,
            'NULL' => self::JSON_TYPE_NULL,
            default => throw new UnexpectedValueException('Given PHP type is not supported in JSON conversion: ' . $type),
        };
    }
}
