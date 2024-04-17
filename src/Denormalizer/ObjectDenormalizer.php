<?php

declare(strict_types=1);

namespace Spiks\UserInputProcessor\Denormalizer;

use Spiks\UserInputProcessor\ConstraintViolation\ConstraintViolationInterface;
use Spiks\UserInputProcessor\ConstraintViolation\MandatoryFieldMissing;
use Spiks\UserInputProcessor\ConstraintViolation\ValueShouldNotBeNull;
use Spiks\UserInputProcessor\ConstraintViolation\WrongPropertyType;
use Spiks\UserInputProcessor\Exception\ValidationError;
use Spiks\UserInputProcessor\ObjectField;
use Spiks\UserInputProcessor\Pointer;

/**
 * Denormalizer for fields where associative arrays are expected.
 *
 * It will fail if indexed array (list) passed. Use {@see ArrayDenormalizer} instead.
 */
final class ObjectDenormalizer
{
    /**
     * Validates and denormalizes passed data.
     *
     * It expects `$data` to be array type, but also accepts additional validation requirements.
     *
     * @param mixed                      $data               Data to validate and denormalize
     * @param Pointer                    $pointer            Pointer containing path to current field
     * @param array<string, ObjectField> $fieldDenormalizers Denormalization rules for each allowed discriminator value
     *
     * @return array<string, mixed> The same array as `$data`, but value of each key may be modified by denormalization functions
     *                              defined in `$staticFields` object
     *
     * @throws ValidationError If `$data` does not meet the requirements of the denormalizer
     */
    public function denormalize(
        mixed $data,
        Pointer $pointer,
        array $fieldDenormalizers,
    ): array {
        /** @var list<ConstraintViolationInterface> $violations */
        $violations = [];

        if (!\is_array($data) || array_is_list($data)) {
            $violations[] = WrongPropertyType::guessGivenType(
                $pointer,
                $data,
                [WrongPropertyType::JSON_TYPE_OBJECT]
            );

            throw new ValidationError($violations);
        }

        /** @var array<string, mixed> $processedData */
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
                $violations = [
                    ...$violations,
                    ...$e->getViolations(),
                ];
            }
        }

        if (\count($violations) > 0) {
            throw new ValidationError($violations);
        }

        return $processedData;
    }
}
