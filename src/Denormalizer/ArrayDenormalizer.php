<?php

declare(strict_types=1);

namespace UserInputProcessor\Denormalizer;

use Closure;
use LogicException;
use UserInputProcessor\ConstraintViolation\ArrayIsTooLong;
use UserInputProcessor\ConstraintViolation\ArrayIsTooShort;
use UserInputProcessor\ConstraintViolation\ConstraintViolationInterface;
use UserInputProcessor\ConstraintViolation\WrongPropertyType;
use UserInputProcessor\Exception\ValidationError;
use UserInputProcessor\Pointer;

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
     * @template TArrayEntry of mixed
     *
     * @psalm-param mixed $data Data to validate and denormalize
     * @psalm-param Pointer $pointer Pointer containing path to current field
     * @psalm-param Closure(mixed, Pointer): TArrayEntry $denormalizer Denormalizer function that will be called for each array entry.
     *                                                                 First parameter of the function will contain value of the entry.
     *                                                                 The second one will contain {@see Pointer} for this entry.
     * @psalm-param int<0,max>|null $minItems Minimum amount of entries in passed array
     * @psalm-param int<0,max>|null $maxItems Maximum amount of entries in passed array
     *
     * @throws ValidationError If `$data` does not meet the requirements of the denormalizer
     *
     * @psalm-return list<TArrayEntry> The same array as `$data`, but modified by `$denormalizer` function applied to each array entry
     */
    public function denormalize(
        mixed $data,
        Pointer $pointer,
        Closure $denormalizer,
        ?int $minItems = null,
        ?int $maxItems = null,
    ): array {
        if (null !== $minItems && null !== $maxItems && $minItems > $maxItems) {
            throw new LogicException('Min items constraint can not be bigger than max items');
        }

        /** @psalm-var list<ConstraintViolationInterface> $violations */
        $violations = [];

        if (!\is_array($data)) {
            $violations[] = WrongPropertyType::guessGivenType($pointer, $data, [WrongPropertyType::JSON_TYPE_ARRAY]);

            throw new ValidationError($violations);
        }

        if (!array_is_list($data)) {
            $violations[] = new WrongPropertyType($pointer, WrongPropertyType::JSON_TYPE_OBJECT, [
                WrongPropertyType::JSON_TYPE_ARRAY,
            ]);

            throw new ValidationError($violations);
        }

        if (null !== $minItems && \count($data) < $minItems) {
            $violations[] = new ArrayIsTooShort($pointer, $minItems);
        }

        if (null !== $maxItems && \count($data) > $maxItems) {
            $violations[] = new ArrayIsTooLong($pointer, $maxItems);
        }

        if (\count($violations) > 0) {
            throw new ValidationError($violations);
        }

        $processedData = [];

        /** @psalm-var TArrayEntry $indexedData */
        foreach ($data as $index => $indexedData) {
            try {
                $processedIndex = $denormalizer($indexedData, Pointer::append($pointer, (string) $index));

                $processedData[] = $processedIndex;
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
