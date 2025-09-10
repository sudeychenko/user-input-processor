<?php

declare(strict_types=1);

namespace UserInputProcessor\Denormalizer;

use Closure;
use UserInputProcessor\ConstraintViolation\ConstraintViolationInterface;
use UserInputProcessor\ConstraintViolation\MandatoryFieldMissing;
use UserInputProcessor\ConstraintViolation\ValueShouldNotBeNull;
use UserInputProcessor\ConstraintViolation\WrongPropertyType;
use UserInputProcessor\Exception\ValidationError;
use UserInputProcessor\Pointer;

/**
 * Denormalizer for fields where associative arrays are expected.
 *
 * It will fail if indexed array (list) passed. Use {@see ArrayDenormalizer} instead.
 *
 * WARNING: This API is experimental and may be removed in the future.
 */
final readonly class ObjectPropertyDenormalizer
{
    /**
     * Validates and denormalizes passed data.
     *
     * It expects `$data` to be array type, but also accepts additional validation requirements.
     *
     * @template TObjectPropertyType of mixed
     *
     * @psalm-param mixed $data Data to validate and denormalize
     * @psalm-param Pointer $pointer Pointer containing path to current field
     * @psalm-param Closure(mixed, Pointer): TObjectPropertyType $denormalizer Denormalizer function that handles denormalization of the field.
     *                                                                         First parameter of the function will contain value of the field.
     *                                                                         The second one will contain {@see Pointer} pointing to the field.
     *
     * @throws ValidationError If `$data` does not meet the requirements of the denormalizer
     *
     * @psalm-return TObjectPropertyType|null
     */
    public function denormalizeNullableObjectProperty(
        mixed $data,
        Pointer $pointer,
        string $objectPropertyName,
        Closure $denormalizer,
    ): mixed {
        /** @psalm-var list<ConstraintViolationInterface> $violations */
        $violations = [];

        $processedData = null;

        if (!\is_array($data) || array_is_list($data)) {
            $violations[] = WrongPropertyType::guessGivenType($pointer, $data, [WrongPropertyType::JSON_TYPE_OBJECT]);

            throw new ValidationError($violations);
        }

        if (!\array_key_exists($objectPropertyName, $data)) {
            $violations[] = new MandatoryFieldMissing(Pointer::append($pointer, $objectPropertyName));

            throw new ValidationError($violations);
        }

        if (null === $data[$objectPropertyName]) {
            return $processedData;
        }

        try {
            $processedData = $denormalizer($data[$objectPropertyName], Pointer::append($pointer, $objectPropertyName));
        } catch (ValidationError $e) {
            $violations = [...$violations, ...$e->getViolations()];
        }

        if (\count($violations) > 0) {
            throw new ValidationError($violations);
        }

        return $processedData;
    }

    /**
     * Validates and denormalizes passed data.
     *
     * It expects `$data` to be array type, but also accepts additional validation requirements.
     *
     * @template TObjectPropertyType of mixed
     *
     * @psalm-param mixed $data Data to validate and denormalize
     * @psalm-param Pointer $pointer Pointer containing path to current field
     * @psalm-param Closure(mixed, Pointer): TObjectPropertyType $denormalizer Denormalizer function that handles denormalization of the field.
     *                                                                         First parameter of the function will contain value of the field.
     *                                                                         The second one will contain {@see Pointer} pointing to the field.
     *
     * @throws ValidationError If `$data` does not meet the requirements of the denormalizer
     *
     * @psalm-return TObjectPropertyType
     */
    public function denormalizeObjectProperty(
        mixed $data,
        Pointer $pointer,
        string $objectPropertyName,
        Closure $denormalizer,
    ): mixed {
        $processedData = $this->denormalizeNullableObjectProperty($data, $pointer, $objectPropertyName, $denormalizer);

        /** @psalm-var list<ConstraintViolationInterface> $violations */
        $violations = [];

        if (null === $processedData) {
            $violations[] = new ValueShouldNotBeNull(Pointer::append($pointer, $objectPropertyName));

            throw new ValidationError($violations);
        }

        return $processedData;
    }
}
