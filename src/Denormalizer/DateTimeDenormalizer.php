<?php

declare(strict_types=1);

namespace UserInputProcessor\Denormalizer;

use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use UserInputProcessor\ConstraintViolation\InvalidDate;
use UserInputProcessor\Exception\ValidationError;
use UserInputProcessor\Pointer;

final readonly class DateTimeDenormalizer
{
    private const string DATE_TIME_FORMAT = DateTimeInterface::RFC3339;

    public function __construct(private StringDenormalizer $stringDenormalizer)
    {
    }

    /**
     * Validates and denormalizes passed data.
     *
     * It expects `$data` to be datetime string type. `$data` must be formatted RFC3339('Y-m-d\TH:i:sP')
     *
     * @psalm-param mixed $data Data to validate and denormalize
     * @psalm-param Pointer $pointer Pointer containing path to current field
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
            throw new ValidationError([new InvalidDate($pointer, \sprintf('date is not valid: %s', $stringDate))]);
        }

        return $dateTime->setTimezone(new DateTimeZone('UTC'));
    }
}
