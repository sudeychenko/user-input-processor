<?php

declare(strict_types=1);

namespace Spiks\UserInputProcessor\Denormalizer;

use DateTimeZone;
use Exception;
use Spiks\UserInputProcessor\ConstraintViolation\InvalidTimeZone;
use Spiks\UserInputProcessor\Exception\ValidationError;
use Spiks\UserInputProcessor\Pointer;

class TimeZoneDenormalizer
{
    public function __construct(private readonly StringDenormalizer $stringDenormalizer)
    {
    }

    public function denormalize(mixed $data, Pointer $pointer): DateTimeZone
    {
        $stringTimeZone = $this->stringDenormalizer->denormalize($data, $pointer, 1);

        try {
            $timeZone = new DateTimeZone($stringTimeZone);
        } catch (Exception) {
            throw new ValidationError([
                new InvalidTimeZone($pointer, sprintf('time zone is not valid: %s', $stringTimeZone)),
            ]);
        }

        return $timeZone;
    }
}
