<?php

declare(strict_types=1);

namespace UserInputProcessor\Denormalizer;

use UserInputProcessor\ConstraintViolation\WrongPropertyType;
use UserInputProcessor\Exception\ValidationError;
use UserInputProcessor\Pointer;

final readonly class NumericStringDenormalizer
{
    public function __construct(private IntegerDenormalizer $integerDenormalizer)
    {
    }

    /**
     * @throws ValidationError If $data has invalid parameters
     */
    public function denormalize(mixed $data, Pointer $pointer, ?int $minimum = null, ?int $maximum = null): int
    {
        if (!is_numeric($data)) {
            throw new ValidationError([
                WrongPropertyType::guessGivenType($pointer, $data, [WrongPropertyType::JSON_TYPE_NUMBER]),
            ]);
        }

        return $this->integerDenormalizer->denormalize((int) $data, $pointer, $minimum, $maximum);
    }
}
