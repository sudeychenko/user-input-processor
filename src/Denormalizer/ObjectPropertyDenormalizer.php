<?php

declare(strict_types=1);

namespace Spiks\UserInputProcessor\Denormalizer;

use Closure;
use Spiks\UserInputProcessor\ConstraintViolation\ConstraintViolationInterface;
use Spiks\UserInputProcessor\ConstraintViolation\MandatoryFieldMissing;
use Spiks\UserInputProcessor\ConstraintViolation\ValueShouldNotBeNull;
use Spiks\UserInputProcessor\ConstraintViolation\WrongPropertyType;
use Spiks\UserInputProcessor\Exception\ValidationError;
use Spiks\UserInputProcessor\Pointer;

/**
 * Denormalizer for fields where associative arrays are expected.
 *
 * It will fail if indexed array (list) passed. Use {@see ArrayDenormalizer} instead.
 */
final class ObjectPropertyDenormalizer
{
    /**
     * Validates and denormalizes passed data.
     *
     * It expects `$data` to be array type, but also accepts additional validation requirements.
     *
     * @template TObjectPropertyType of mixed
     *
     * @param mixed                                        $data         Data to validate and denormalize
     * @param Pointer                                      $pointer      Pointer containing path to current field
     * @param Closure(mixed, Pointer): TObjectPropertyType $denormalizer Denormalizer function that handles denormalization of the field.
     *                                                                   First parameter of the function will contain value of the field.
     *                                                                   The second one will contain {@see Pointer} pointing to the field.
     *
     * @throws ValidationError If `$data` does not meet the requirements of the denormalizer
     *
     * @return TObjectPropertyType|null
     */
    public function denormalizeNullableObjectProperty(
        mixed $data,
        Pointer $pointer,
        string $objectPropertyName,
        Closure $denormalizer,
    ): mixed {
        /** @var list<ConstraintViolationInterface> $violations */
        $violations = [];

        $processedData = null;

        if (!\is_array($data) || !self::isAssocArray($data)) {
            $violations[] = WrongPropertyType::guessGivenType(
                $pointer,
                $data,
                [WrongPropertyType::JSON_TYPE_OBJECT]
            );

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
            $processedData = $denormalizer(
                $data[$objectPropertyName],
                Pointer::append($pointer, $objectPropertyName)
            );
        } catch (ValidationError $e) {
            $violations = [
                ...$violations,
                ...$e->getViolations(),
            ];
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
     * @param mixed                                        $data         Data to validate and denormalize
     * @param Pointer                                      $pointer      Pointer containing path to current field
     * @param Closure(mixed, Pointer): TObjectPropertyType $denormalizer Denormalizer function that handles denormalization of the field.
     *                                                                   First parameter of the function will contain value of the field.
     *                                                                   The second one will contain {@see Pointer} pointing to the field.
     *
     * @throws ValidationError If `$data` does not meet the requirements of the denormalizer
     *
     * @return TObjectPropertyType
     */
    public function denormalizeObjectProperty(
        mixed $data,
        Pointer $pointer,
        string $objectPropertyName,
        Closure $denormalizer,
    ): mixed {
        $processedData = $this->denormalizeNullableObjectProperty(
            $data,
            $pointer,
            $objectPropertyName,
            $denormalizer
        );

        /** @var list<ConstraintViolationInterface> $violations */
        $violations = [];

        if (null === $processedData) {
            $violations[] = new ValueShouldNotBeNull(Pointer::append($pointer, $objectPropertyName));

            throw new ValidationError($violations);
        }

        return $processedData;
    }

    /**
     * @param array<array-key, mixed> $array
     */
    private static function isAssocArray(array $array): bool
    {
        return array_keys($array) !== range(0, \count($array) - 1);
    }
}
