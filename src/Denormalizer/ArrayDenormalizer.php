<?php

declare(strict_types=1);

namespace Spiks\UserInputProcessor\Denormalizer;

use Closure;
use LogicException;
use Spiks\UserInputProcessor\ConstraintViolation\ArrayIsTooLong;
use Spiks\UserInputProcessor\ConstraintViolation\ArrayIsTooShort;
use Spiks\UserInputProcessor\ConstraintViolation\ConstraintViolationInterface;
use Spiks\UserInputProcessor\ConstraintViolation\WrongPropertyType;
use Spiks\UserInputProcessor\Exception\ValidationError;
use Spiks\UserInputProcessor\Pointer;

/**
 * Denormalizer for fields where indexed array (lists) is expected.
 *
 * It will fail if associative array passed. Use {@see ObjectPropertyDenormalizer} instead.
 */
final class ArrayDenormalizer
{
    /**
     * Validates and denormalizes passed data.
     *
     * It expects `$data` to be array type, but also accepts additional validation requirements.
     *
     * @param mixed                          $data         Data to validate and denormalize
     * @param Pointer                        $pointer      Pointer containing path to current field
     * @param Closure(mixed, Pointer): mixed $denormalizer Denormalizer function that will be called for each array entry.
     *                                                     First parameter of the function will contain value of the entry.
     *                                                     The second one will contain {@see Pointer} for this entry.
     * @param int|null                       $minItems     Minimum amount of entries in passed array
     * @param int|null                       $maxItems     Maximum amount of entries in passed array
     *
     * @throws ValidationError If `$data` does not meet the requirements of the denormalizer
     *
     * @return array The same array as `$data`, but modified by `$denormalizer` function applied to each array entry
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

        /** @var list<ConstraintViolationInterface> $violations */
        $violations = [];

        if (!\is_array($data)) {
            $violations[] = WrongPropertyType::guessGivenType(
                $pointer,
                $data,
                [WrongPropertyType::JSON_TYPE_ARRAY]
            );

            throw new ValidationError($violations);
        }

        if (!array_is_list($data)) {
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

        if (\count($violations) > 0) {
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
