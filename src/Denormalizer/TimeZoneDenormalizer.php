<?php

declare(strict_types=1);

namespace UserInputProcessor\Denormalizer;

use DateTimeZone;
use Exception;
use UserInputProcessor\ConstraintViolation\InvalidTimeZone;
use UserInputProcessor\Exception\ValidationError;
use UserInputProcessor\Pointer;

final readonly class TimeZoneDenormalizer
{
    public function __construct(private StringDenormalizer $stringDenormalizer)
    {
    }

    /**
     * Validates and denormalizes passed data.
     *
     * It expects `$data` to be string of timezone (https://www.php.net/manual/en/timezones.php).
     *
     * @psalm-param mixed $data Data to validate and denormalize
     * @psalm-param Pointer $pointer Pointer containing path to current field
     *
     * @throws ValidationError If `$data` does not meet the requirements of the denormalizer
     *
     * @psalm-return DateTimeZone The same string as the one that was passed to `$data` argument
     */
    public function denormalize(mixed $data, Pointer $pointer): DateTimeZone
    {
        $stringTimeZone = $this->stringDenormalizer->denormalize($data, $pointer, 1);

        try {
            $timeZone = new DateTimeZone($stringTimeZone);
        } catch (Exception) {
            throw new ValidationError([
                new InvalidTimeZone($pointer, \sprintf('time zone is not valid: %s', $stringTimeZone)),
            ]);
        }

        return $timeZone;
    }
}
