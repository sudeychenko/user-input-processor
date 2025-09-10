<?php

declare(strict_types=1);

namespace UserInputProcessor\Denormalizer;

use UserInputProcessor\ConstraintViolation\ConstraintViolationInterface;
use UserInputProcessor\ConstraintViolation\MandatoryFieldMissing;
use UserInputProcessor\ConstraintViolation\ValueShouldNotBeNull;
use UserInputProcessor\ConstraintViolation\WrongPropertyType;
use UserInputProcessor\Exception\ValidationError;
use UserInputProcessor\ObjectField;
use UserInputProcessor\Pointer;

/**
 * Denormalizer for fields where associative arrays are expected.
 *
 * It will fail if indexed array (list) passed. Use {@see ArrayDenormalizer} instead.
 */
final readonly class ObjectDenormalizer
{
    /**
     * Validates and denormalizes passed data.
     *
     * It expects `$data` to be array type, but also accepts additional validation requirements.
     *
     * @psalm-param mixed $data Data to validate and denormalize
     * @psalm-param Pointer $pointer Pointer containing path to current field
     * @psalm-param array<string, ObjectField> $fieldDenormalizers Denormalization rules for each allowed discriminator value
     *
     * @throws ValidationError If `$data` does not meet the requirements of the denormalizer
     *
     * @psalm-return array<string, mixed> The same array as `$data`, but value of each key may be modified by denormalization functions
     *                              defined in `$staticFields` object
     */
    public function denormalize(mixed $data, Pointer $pointer, array $fieldDenormalizers): array
    {
        /** @psalm-var list<ConstraintViolationInterface> $violations */
        $violations = [];

        if (!\is_array($data) || array_is_list($data)) {
            $violations[] = WrongPropertyType::guessGivenType($pointer, $data, [WrongPropertyType::JSON_TYPE_OBJECT]);

            throw new ValidationError($violations);
        }

        /** @psalm-var array<string, mixed> $processedData */
        $processedData = [];

        foreach ($fieldDenormalizers as $fieldName => $fieldDefinition) {
            if (!\array_key_exists($fieldName, $data)) {
                if ($fieldDefinition->isMandatory()) {
                    $violations[] = new MandatoryFieldMissing(Pointer::append($pointer, $fieldName));
                }

                continue;
            }

            if (null === $data[$fieldName]) {
                if (!$fieldDefinition->isNullable()) {
                    $violations[] = new ValueShouldNotBeNull(Pointer::append($pointer, $fieldName));
                }

                $processedData[$fieldName] = null;

                continue;
            }

            try {
                /**
                 * @psalm-suppress MixedAssignment
                 */
                $processedData[$fieldName] = $fieldDefinition->getDenormalizer()(
                    $data[$fieldName],
                    Pointer::append($pointer, $fieldName)
                );
            } catch (ValidationError $e) {
                $violations = [...$violations, ...$e->getViolations()];
            }
        }

        if (\count($violations) > 0) {
            throw new ValidationError($violations);
        }

        return $processedData;
    }
}
