<?php

declare(strict_types=1);

namespace Spiks\UserInputProcessor\Denormalizer;

use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use LogicException;
use Spiks\UserInputProcessor\ConstraintViolation\InvalidDate;
use Spiks\UserInputProcessor\ConstraintViolation\InvalidDateRange;
use Spiks\UserInputProcessor\DateTimeRange;
use Spiks\UserInputProcessor\Exception\ValidationError;
use Spiks\UserInputProcessor\ObjectField;
use Spiks\UserInputProcessor\Pointer;

class DateTimeDenormalizer
{
    private const DATE_ONLY_FORMAT = 'Y-m-d';
    private const DATE_TIME_FORMAT = DateTimeInterface::RFC3339;

    public function __construct(
        private readonly StringDenormalizer $stringDenormalizer,
        private readonly ObjectDenormalizer $objectDenormalizer,
        private readonly TimeZoneDenormalizer $timeZoneDenormalizer,
    ) {
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
        return $this->denormalizeByFormat(data: $data, pointer: $pointer, format: self::DATE_TIME_FORMAT);
    }

    /**
     * Validates and denormalizes passed data.
     *
     * It expects `$data` to be datetime string type. `$data` must be formatted ('Y-m-d').
     * In the response, the time will be set at 00:00.
     *
     * @param mixed   $data    Data to validate and denormalize
     * @param Pointer $pointer Pointer containing path to current field
     *
     * @psalm-return DateTimeImmutable The same datetime as the one that was passed to `$data` argument
     *
     * @throws ValidationError If `$data` does not meet the requirements of the denormalizer
     */
    public function denormalizeDateOnly(mixed $data, Pointer $pointer): DateTimeImmutable
    {
        return $this->denormalizeByFormat(data: $data, pointer: $pointer, format: self::DATE_ONLY_FORMAT)->setTime(hour: 0, minute: 0);
    }

    public function denormalizeDateRangeWithDateTimeZone(mixed $data, Pointer $pointer): DateTimeRange
    {
        /**
         * @psalm-var array{
         *     from: DateTimeImmutable,
         *     timeZone: DateTimeZone,
         *     to: DateTimeImmutable,
         * } $dateTimeRange
         */
        $dateTimeRange = $this->objectDenormalizer->denormalize($data, $pointer, [
            'from' => new ObjectField(
                fn (mixed $data, Pointer $pointer): DateTimeImmutable => $this->denormalizeDateOnly($data, $pointer),
            ),
            'timeZone' => new ObjectField(
                fn (mixed $data, Pointer $pointer): DateTimeZone => $this->timeZoneDenormalizer->denormalize(
                    $data,
                    $pointer,
                ),
            ),
            'to' => new ObjectField(
                fn (mixed $data, Pointer $pointer): DateTimeImmutable => $this->denormalizeDateOnly($data, $pointer),
            ),
        ]);

        if ($dateTimeRange['to'] < $dateTimeRange['from']) {
            throw new ValidationError([new InvalidDateRange(pointer: $pointer)]);
        }

        $from = $this->getLowerBound(date: $dateTimeRange['from'], timeZone: $dateTimeRange['timeZone']);
        $to = $this->getUpperBound(date: $dateTimeRange['to'], timeZone: $dateTimeRange['timeZone']);

        return new DateTimeRange(from: $from, to: $to);
    }

    /**
     * @throws ValidationError If `$data` does not meet the requirements of the denormalizer
     */
    private function denormalizeByFormat(mixed $data, Pointer $pointer, string $format): DateTimeImmutable
    {
        $stringDate = $this->stringDenormalizer->denormalize($data, $pointer, 1);
        $dateTime = DateTimeImmutable::createFromFormat($format, $stringDate);

        if (false === $dateTime || $dateTime->format($format) !== $stringDate) {
            throw new ValidationError([new InvalidDate($pointer, sprintf('date is not valid: %s', $stringDate))]);
        }

        return $dateTime->setTimezone(new DateTimeZone('UTC'));
    }

    /**
     * @throws ValidationError If `$data` does not meet the requirements of the denormalizer
     */
    private function getLowerBound(DateTimeImmutable $date, DateTimeZone $timeZone): DateTimeImmutable
    {
        if (0 !== $date->getOffset()) {
            throw new LogicException('Timezone of argument $date must be the same as system timezone, that is UTC.');
        }

        $offsetSeconds = $timeZone->getOffset(new DateTimeImmutable());
        $fromTimestamp = $date->setTime(hour: 0, minute: 0)->getTimestamp() - $offsetSeconds;

        return (new DateTimeImmutable())->setTimestamp($fromTimestamp);
    }

    /**
     * @throws ValidationError If `$data` does not meet the requirements of the denormalizer
     */
    private function getUpperBound(DateTimeImmutable $date, DateTimeZone $timeZone): DateTimeImmutable
    {
        if (0 !== $date->getOffset()) {
            throw new LogicException('Timezone of argument $date must be the same as system timezone, that is UTC.');
        }

        $offsetSeconds = $timeZone->getOffset(new DateTimeImmutable());
        $fromTimestamp = $date->setTime(23, 59, 59)->getTimestamp() - $offsetSeconds;

        return (new DateTimeImmutable())->setTimestamp($fromTimestamp);
    }
}
