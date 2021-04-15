<?php

declare(strict_types=1);

namespace Flaksp\UserInputDeserializer\ConstraintViolation;

use Flaksp\UserInputDeserializer\JsonPointer;
use RuntimeException;

final class WrongPropertyType implements ConstraintViolationInterface
{
    public const JSON_TYPE_ARRAY = 'array';
    public const JSON_TYPE_BOOLEAN = 'boolean';
    public const JSON_TYPE_FLOAT = 'float';
    public const JSON_TYPE_INTEGER = 'integer';
    public const JSON_TYPE_NULL = 'null';
    public const JSON_TYPE_OBJECT = 'object';
    public const JSON_TYPE_STRING = 'string';

    public const TYPE = 'wrong_property_type';

    public function __construct(
        private JsonPointer $pointer,
        private string $givenType,
        private array $allowedTypes,
    ) {
    }

    public static function getType(): string
    {
        return self::TYPE;
    }

    public static function guessGivenType(
        JsonPointer $pointer,
        $givenValue,
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
            'Property "%s" is %s type, but only following types are allowed: %s',
            $this->pointer->getPointer(),
            $this->givenType,
            implode(', ', $this->allowedTypes)
        );
    }

    public function getGivenType(): string
    {
        return $this->givenType;
    }

    public function getPointer(): JsonPointer
    {
        return $this->pointer;
    }

    private static function getJsonTypeFromValue($value): string
    {
        return match (\gettype($value)) {
            'boolean' => self::JSON_TYPE_BOOLEAN,
            'integer' => self::JSON_TYPE_INTEGER,
            'double' => self::JSON_TYPE_FLOAT,
            'string' => self::JSON_TYPE_STRING,
            'array' => self::JSON_TYPE_ARRAY,
            'NULL' => self::JSON_TYPE_NULL,
            default => throw new RuntimeException('Given PHP type is not supported in JSON conversion: ' . $value),
        };
    }
}
