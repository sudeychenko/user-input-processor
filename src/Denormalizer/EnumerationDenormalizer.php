<?php

declare(strict_types=1);

namespace UserInputProcessor\Denormalizer;

use BackedEnum;
use UserInputProcessor\ConstraintViolation\EnumValueIsNotAllowed;
use UserInputProcessor\Exception\ValidationError;
use UserInputProcessor\Pointer;
use ValueError;

final readonly class EnumerationDenormalizer
{
    public function __construct(private StringDenormalizer $stringDenormalizer)
    {
    }

    /**
     * Validates and denormalizes passed data.
     *
     * It expects `$enumClassName` to be class-string type, but also accepts additional validation requirements.
     * It expects `$allowedValues` to be list of enum type, but also accepts additional validation requirements.
     *
     * @template T of BackedEnum
     *
     * @psalm-param mixed $data Data to validate and denormalize
     * @psalm-param Pointer $pointer Pointer containing path to current field
     * @psalm-param class-string<T> $enumClassName class name of enum
     * @psalm-param list<T> $allowedValues `$data` must contain only the elements listed in the `$allowedValues`
     *
     * @throws ValidationError If `$data` does not meet the requirements of the denormalizer
     *
     * @psalm-return T the same enum object as the one that was passed to `$data` argument
     */
    public function denormalize(
        mixed $data,
        Pointer $pointer,
        string $enumClassName,
        ?array $allowedValues = null,
    ): BackedEnum {
        $denormalizedData = $this->stringDenormalizer->denormalize($data, $pointer);

        try {
            $enum = $enumClassName::from($denormalizedData);
        } catch (ValueError) {
            throw new ValidationError([new EnumValueIsNotAllowed($pointer, $enumClassName::cases())]);
        }

        if (null !== $allowedValues && !\in_array($enum, $allowedValues, true)) {
            throw new ValidationError([new EnumValueIsNotAllowed($pointer, $allowedValues)]);
        }

        return $enum;
    }
}
