<?php

declare(strict_types=1);

namespace UserInputProcessor\ConstraintViolation;

use Override;
use UnexpectedValueException;
use UserInputProcessor\Pointer;

final readonly class WrongPropertyType implements ConstraintViolationInterface
{
    public const string JSON_TYPE_ARRAY = 'array';
    public const string JSON_TYPE_BOOLEAN = 'boolean';
    public const string JSON_TYPE_FLOAT = 'float';
    public const string JSON_TYPE_NULL = 'null';
    public const string JSON_TYPE_NUMBER = 'number';
    public const string JSON_TYPE_OBJECT = 'object';
    public const string JSON_TYPE_STRING = 'string';

    public const string TYPE = 'wrong_property_type';

    /**
     * @psalm-param self::JSON_TYPE_* $givenType
     * @psalm-param non-empty-list<self::JSON_TYPE_*> $allowedTypes
     */
    public function __construct(private Pointer $pointer, private string $givenType, private array $allowedTypes)
    {
    }

    #[Override]
    public static function getType(): string
    {
        return self::TYPE;
    }

    /**
     * @psalm-param non-empty-list<self::JSON_TYPE_*> $allowedTypes
     *
     * @psalm-return static
     */
    public static function guessGivenType(Pointer $pointer, mixed $givenValue, array $allowedTypes): self
    {
        return new self($pointer, self::getJsonTypeFromValue($givenValue), $allowedTypes);
    }

    /**
     * @psalm-return non-empty-list<self::JSON_TYPE_*> $allowedTypes
     */
    public function getAllowedTypes(): array
    {
        return $this->allowedTypes;
    }

    #[Override]
    public function getDescription(): string
    {
        return \sprintf(
            'Property is %s type, but only following types are allowed: %s',
            $this->givenType,
            implode(', ', $this->allowedTypes),
        );
    }

    /**
     * @psalm-return self::JSON_TYPE_*
     */
    public function getGivenType(): string
    {
        return $this->givenType;
    }

    #[Override]
    public function getPointer(): Pointer
    {
        return $this->pointer;
    }

    /**
     * @psalm-return self::JSON_TYPE_*
     */
    private static function getJsonTypeFromValue(mixed $value): string
    {
        if (\is_array($value)) {
            return array_is_list($value) ? self::JSON_TYPE_ARRAY : self::JSON_TYPE_OBJECT;
        }

        $type = \gettype($value);

        return match ($type) {
            'boolean' => self::JSON_TYPE_BOOLEAN,
            'integer' => self::JSON_TYPE_NUMBER,
            'double' => self::JSON_TYPE_FLOAT,
            'string' => self::JSON_TYPE_STRING,
            'NULL' => self::JSON_TYPE_NULL,
            default => throw new UnexpectedValueException(
                'Given PHP type is not supported in JSON conversion: ' . $type,
            ),
        };
    }
}
