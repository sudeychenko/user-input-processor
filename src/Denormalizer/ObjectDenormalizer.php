<?php

declare(strict_types=1);

namespace Flaksp\UserInputProcessor\Denormalizer;

use Flaksp\UserInputProcessor\ConstraintViolation\ConstraintViolationCollection;
use Flaksp\UserInputProcessor\ConstraintViolation\MandatoryFieldMissing;
use Flaksp\UserInputProcessor\ConstraintViolation\ValueShouldNotBeNull;
use Flaksp\UserInputProcessor\ConstraintViolation\WrongDiscriminatorValue;
use Flaksp\UserInputProcessor\ConstraintViolation\WrongPropertyType;
use Flaksp\UserInputProcessor\Exception\ValidationError;
use Flaksp\UserInputProcessor\ObjectDiscriminatorFields;
use Flaksp\UserInputProcessor\ObjectStaticFields;
use Flaksp\UserInputProcessor\Pointer;
use LogicException;

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
     * This method should be used when denormalization may differ depending on specific field value.
     * Such field called "discriminator". All possible values of the discriminator are static and known
     * in advance. For example, if you are denormalizing OAuth 2 grant, `grant_type` field may be used as
     * discriminator, and, for example, all possible values of the field will be `authorization_code`, `password`,
     * `client_credentials`, `refresh_token`, and so on. Depending on the value of the discriminator, denormalization
     * may differ, and returned by the denormalizer value may also differ.
     *
     * @param mixed                     $data                   Data to validate and denormalize
     * @param Pointer                   $pointer                Pointer containing path to current field
     * @param string                    $discriminatorFieldName field name of the discriminator
     * @param ObjectDiscriminatorFields $discriminatorFields    Denormalization rules for each allowed discriminator value
     *
     * @throws ValidationError If `$data` does not meet the requirements of the denormalizer
     *
     * @return array The same array as `$data`, but value of each key may be modified by denormalization functions
     *               defined in `$discriminatorFields` object
     */
    public function denormalizeDynamicFields(
        mixed $data,
        Pointer $pointer,
        string $discriminatorFieldName,
        ObjectDiscriminatorFields $discriminatorFields,
    ): array {
        $violations = new ConstraintViolationCollection();

        if (!\is_array($data) || !self::isAssocArray($data)) {
            $violations[] = WrongPropertyType::guessGivenType(
                $pointer,
                $data,
                [WrongPropertyType::JSON_TYPE_OBJECT]
            );

            throw new ValidationError($violations);
        }

        if (!\array_key_exists($discriminatorFieldName, $data)) {
            $violations[] = new MandatoryFieldMissing(
                Pointer::append($pointer, $discriminatorFieldName),
            );

            throw new ValidationError($violations);
        }

        $discriminatorValue = $data[$discriminatorFieldName];

        if (!\is_string($discriminatorValue)) {
            $violations[] = WrongPropertyType::guessGivenType(
                Pointer::append($pointer, $discriminatorFieldName),
                $discriminatorValue,
                [WrongPropertyType::JSON_TYPE_STRING]
            );

            throw new ValidationError($violations);
        }

        if (!\in_array($discriminatorValue, $discriminatorFields->getPossibleDiscriminatorValues(), true)) {
            $violations[] = new WrongDiscriminatorValue(
                Pointer::append($pointer, $discriminatorFieldName),
                $discriminatorFields->getPossibleDiscriminatorValues(),
            );

            throw new ValidationError($violations);
        }

        /** @var array $processedData */
        $processedData = $this->denormalizeStaticFields(
            $data,
            $pointer,
            $discriminatorFields->getStaticFieldsByDiscriminatorValue($discriminatorValue),
        );

        if (\array_key_exists($discriminatorFieldName, $processedData)) {
            throw new LogicException('The same field name as discriminator field name can not be used within ObjectStaticFields declarations');
        }

        $processedData[$discriminatorFieldName] = $discriminatorValue;

        return $processedData;
    }

    /**
     * Validates and denormalizes passed data.
     *
     * It expects `$data` to be array type, but also accepts additional validation requirements.
     *
     * @param mixed              $data         Data to validate and denormalize
     * @param Pointer            $pointer      Pointer containing path to current field
     * @param ObjectStaticFields $staticFields Denormalization rules for each allowed discriminator value
     *
     * @throws ValidationError If `$data` does not meet the requirements of the denormalizer
     *
     * @return array The same array as `$data`, but value of each key may be modified by denormalization functions
     *               defined in `$staticFields` object
     */
    public function denormalizeStaticFields(
        mixed $data,
        Pointer $pointer,
        ObjectStaticFields $staticFields,
    ): array {
        $violations = new ConstraintViolationCollection();

        if (!\is_array($data) || !self::isAssocArray($data)) {
            $violations[] = WrongPropertyType::guessGivenType(
                $pointer,
                $data,
                [WrongPropertyType::JSON_TYPE_OBJECT]
            );

            throw new ValidationError($violations);
        }

        $processedData = [];

        foreach ($staticFields->getFields() as $fieldName => $fieldDefinition) {
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
                $processedField = $fieldDefinition->getDenormalizer()(
                    $data[$fieldName],
                    Pointer::append($pointer, $fieldName)
                );

                $processedData[$fieldName] = $processedField;
            } catch (ValidationError $e) {
                $violations->addAll($e->getViolations());
            }
        }

        if ($violations->isNotEmpty()) {
            throw new ValidationError($violations);
        }

        return $processedData;
    }

    private static function isAssocArray(array $array): bool
    {
        return array_keys($array) !== range(0, \count($array) - 1);
    }
}
