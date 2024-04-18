<?php

declare(strict_types=1);

namespace Spiks\UserInputProcessor\Denormalizer;

use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use Spiks\UserInputProcessor\ConstraintViolation\InvalidDate;
use Spiks\UserInputProcessor\Exception\ValidationError;
use Spiks\UserInputProcessor\Pointer;

class DateTimeDenormalizer
{
    private const DATE_TIME_FORMAT = DateTimeInterface::RFC3339;

    public function __construct(private readonly StringDenormalizer $stringDenormalizer)
    {
    }

    /**
     * Validates and denormalizes passed data.
     *
     * It expects `$data` to be datetime string type. `$data` must be formatted RFC3339('Y-m-d\TH:i:sP')
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
        $stringDate = $this->stringDenormalizer->denormalize($data, $pointer, 1);
        $dateTime = DateTimeImmutable::createFromFormat(self::DATE_TIME_FORMAT, $stringDate);

        if (false === $dateTime || $dateTime->format(self::DATE_TIME_FORMAT) !== $stringDate) {
            throw new ValidationError([new InvalidDate($pointer, sprintf('date is not valid: %s', $stringDate))]);
        }

        return $dateTime->setTimezone(new DateTimeZone('UTC'));
    }
}
