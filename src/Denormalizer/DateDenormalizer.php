<?php

declare(strict_types=1);

namespace Spiks\UserInputProcessor\Denormalizer;

use DateTimeImmutable;
use DateTimeZone;
use Spiks\UserInputProcessor\ConstraintViolation\InvalidDate;
use Spiks\UserInputProcessor\Exception\ValidationError;
use Spiks\UserInputProcessor\Pointer;

class DateDenormalizer
{
    private const DATE_FORMAT = 'Y-m-d';

    private const DATE_TIME_ZONE = 'UTC';

    public function __construct(private readonly StringDenormalizer $stringDenormalizer)
    {
    }

    /**
     * Validates and denormalizes passed data.
     *
     * It expects `$data` to be date string type. `$data` must be formatted ISO 8601('Y-m-d')
     *
     * @param mixed   $data    Data to validate and denormalize
     * @param Pointer $pointer Pointer containing path to current field
     *
     * @psalm-return DateTimeImmutable The same datetime as the one that was passed to `$data` argument
     *
     * @throws ValidationError If `$data` does not meet the requirements of the denormalizer
     */
    public function denormalize(mixed $data, Pointer $pointer): DateTimeImmutable
    {
        $stringDate = $this->stringDenormalizer->denormalize(data: $data, pointer: $pointer, minLength: 1);

        $dateTime = DateTimeImmutable::createFromFormat(self::DATE_FORMAT, $stringDate);

        if (false === $dateTime || $dateTime->format(format: self::DATE_FORMAT) !== $stringDate) {
            throw new ValidationError([new InvalidDate($pointer, sprintf('date is not valid: %s', $stringDate))]);
        }

        $timeZone = new DateTimeZone(self::DATE_TIME_ZONE);

        return $dateTime->setTimezone($timeZone);
    }
}
