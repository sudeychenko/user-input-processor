<?php

declare(strict_types=1);

namespace Flaksp\UserInputProcessor\Denormalizer;

use Closure;
use Flaksp\UserInputProcessor\ConstraintViolation\ArrayIsTooLong;
use Flaksp\UserInputProcessor\ConstraintViolation\ArrayIsTooShort;
use Flaksp\UserInputProcessor\ConstraintViolation\ConstraintViolationCollection;
use Flaksp\UserInputProcessor\ConstraintViolation\WrongPropertyType;
use Flaksp\UserInputProcessor\Exception\ValidationError;
use Flaksp\UserInputProcessor\Pointer;
use LogicException;

final class ArrayDenormalizer
{
    /**
     * @throws ValidationError If $data has invalid parameters
     */
    public function denormalize(
        mixed $data,
        Pointer $pointer,
        Closure $denormalizer,
        int $minItems = null,
        int $maxItems = null,
    ): array {
        if (null !== $minItems && null !== $maxItems && $minItems > $maxItems) {
            throw new LogicException('Min items constraint can not be bigger than max items');
        }

        $violations = new ConstraintViolationCollection();

        if (!\is_array($data)) {
            $violations[] = WrongPropertyType::guessGivenType(
                $pointer,
                $data,
                [WrongPropertyType::JSON_TYPE_ARRAY]
            );

            throw new ValidationError($violations);
        }

        if (!self::isIndexedArray($data)) {
            $violations[] = new WrongPropertyType(
                $pointer,
                WrongPropertyType::JSON_TYPE_OBJECT,
                [WrongPropertyType::JSON_TYPE_ARRAY]
            );

            throw new ValidationError($violations);
        }

        if (null !== $minItems && \count($data) < $minItems) {
            $violations[] = new ArrayIsTooShort(
                $pointer,
                $minItems
            );
        }

        if (null !== $maxItems && \count($data) > $maxItems) {
            $violations[] = new ArrayIsTooLong(
                $pointer,
                $maxItems
            );
        }

        if ($violations->isNotEmpty()) {
            throw new ValidationError($violations);
        }

        $processedData = [];

        foreach ($data as $index => $indexedData) {
            try {
                $processedIndex = $denormalizer(
                    $indexedData,
                    Pointer::append($pointer, $index)
                );

                $processedData[$index] = $processedIndex;
            } catch (ValidationError $e) {
                $violations->addAll($e->getViolations());
            }
        }

        if ($violations->isNotEmpty()) {
            throw new ValidationError($violations);
        }

        return $processedData;
    }

    private static function isIndexedArray(array $array): bool
    {
        return array_keys($array) === range(0, \count($array) - 1);
    }
}
