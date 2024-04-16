<?php

declare(strict_types=1);

namespace Spiks\UserInputProcessor\Denormalizer;

use Closure;
use Spiks\UserInputProcessor\ConstraintViolation\ArrayIsNotUnique;
use Spiks\UserInputProcessor\Exception\ValidationError;
use Spiks\UserInputProcessor\Pointer;

class UniqueArrayDenormalizer
{
    public function __construct(
        private readonly ArrayDenormalizer $arrayDenormalizer,
    ) {
    }

    /**
     * @template TArrayEntry of mixed
     *
     * @param Closure(mixed, Pointer): TArrayEntry $denormalizer      Denormalizer function that will be called for each array entry.
     *                                                                First parameter of the function will contain value of the entry.
     * @param Closure(TArrayEntry): string         $uniqueKeyProvider Function to get the key by which the uniqueness of the array will be checked
     * @param int<0,max>|null                      $minItems          Minimum amount of entries in passed array
     * @param int<0,max>|null                      $maxItems          Maximum amount of entries in passed array*
     *
     * @throws ValidationError
     *
     * @return list<TArrayEntry>
     */
    public function denormalize(
        mixed $data,
        Pointer $pointer,
        Closure $denormalizer,
        Closure $uniqueKeyProvider,
        ?int $minItems = null,
        ?int $maxItems = null,
    ): array {
        $processedArray = $this->arrayDenormalizer->denormalize(
            data: $data,
            pointer: $pointer,
            denormalizer: $denormalizer,
            minItems: $minItems,
            maxItems: $maxItems,
        );

        if ($this->isUnique(processedArray: $processedArray, uniqueKeyProvider: $uniqueKeyProvider)) {
            return $processedArray;
        }

        throw new ValidationError([new ArrayIsNotUnique($pointer)]);
    }

    /**
     * @template TArrayEntry
     *
     * @psalm-param list<TArrayEntry> $processedArray
     * @psalm-param Closure(TArrayEntry $item): string $uniqueKeyProvider
     */
    private function isUnique(array $processedArray, Closure $uniqueKeyProvider): bool
    {
        if (\count($processedArray) < 2) {
            return true;
        }

        $assocArray = [];

        foreach ($processedArray as $item) {
            $uniqueKey = $uniqueKeyProvider($item);

            if (\array_key_exists($uniqueKey, $assocArray)) {
                return false;
            }

            $assocArray[$uniqueKey] = $uniqueKey;
        }

        return true;
    }
}
