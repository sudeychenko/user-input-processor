<?php

declare(strict_types=1);

namespace Flaksp\UserInputProcessor\Denormalizer;

use Flaksp\UserInputProcessor\ConstraintViolation\ConstraintViolationCollection;
use Flaksp\UserInputProcessor\ConstraintViolation\MandatoryFieldMissing;
use Flaksp\UserInputProcessor\ConstraintViolation\WrongDiscriminatorValue;
use Flaksp\UserInputProcessor\ConstraintViolation\WrongPropertyType;
use Flaksp\UserInputProcessor\Exception\ValidationError;
use Flaksp\UserInputProcessor\JsonPointer;
use Flaksp\UserInputProcessor\ObjectDiscriminatorFields;
use Flaksp\UserInputProcessor\ObjectStaticFields;

final class ObjectDenormalizer
{
    public static function isAssocArray(array $array): bool
    {
        if ([] === $array) {
            return false;
        }

        return array_keys($array) !== range(0, \count($array) - 1);
    }

    public function denormalizeDynamicFields(
        mixed $data,
        string $discriminatorFieldName,
        ObjectDiscriminatorFields $discriminatorFields,
        JsonPointer $pointer,
        bool $isNullable = false,
    ): ?array {
        if (null === $data && $isNullable) {
            return null;
        }

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
                JsonPointer::append($pointer, $discriminatorFieldName),
            );

            throw new ValidationError($violations);
        }

        if (!\in_array($data[$discriminatorFieldName], $discriminatorFields->getPossibleDiscriminatorValues(), true)) {
            $violations[] = new WrongDiscriminatorValue(
                JsonPointer::append($pointer, $discriminatorFieldName),
                $discriminatorFields->getPossibleDiscriminatorValues(),
            );

            throw new ValidationError($violations);
        }

        return $this->denormalizeStaticFields(
            $data,
            $discriminatorFields->getStaticFieldsByDiscriminatorValue($data[$discriminatorFieldName]),
            $pointer,
            isNullable: false,
        );
    }

    public function denormalizeStaticFields(
        mixed $data,
        ObjectStaticFields $staticFields,
        JsonPointer $pointer,
        bool $isNullable = false,
    ): ?array {
        if (null === $data && $isNullable) {
            return null;
        }

        $violations = new ConstraintViolationCollection();

        if (!\is_array($data) || !self::isAssocArray($data)) {
            $violations[] = WrongPropertyType::guessGivenType(
                $pointer,
                $data,
                [WrongPropertyType::JSON_TYPE_OBJECT]
            );

            throw new ValidationError($violations);
        }

        foreach ($staticFields->getFields() as $fieldName => $fieldDefinition) {
            if (!\array_key_exists($fieldName, $data)) {
                if ($fieldDefinition->isMandatory()) {
                    $violations[] = new MandatoryFieldMissing(JsonPointer::append($pointer, $fieldName));
                }

                continue;
            }

            try {
                $data[$fieldName] = $fieldDefinition->getDenormalizer()(
                    $data[$fieldName],
                    JsonPointer::append($pointer, $fieldName)
                );
            } catch (ValidationError $e) {
                $violations->addAll($e->getViolations());
            }
        }

        if ($violations->isNotEmpty()) {
            throw new ValidationError($violations);
        }

        return $data;
    }
}
