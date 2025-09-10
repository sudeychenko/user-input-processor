<?php

declare(strict_types=1);

namespace UserInputProcessor\Denormalizer;

use DateTimeImmutable;
use DateTimeZone;
use LogicException;
use UserInputProcessor\ConstraintViolation\InvalidDateRange;
use UserInputProcessor\DateTimeRange;
use UserInputProcessor\Exception\ValidationError;
use UserInputProcessor\ObjectField;
use UserInputProcessor\Pointer;

/**
 * The denormalizer returns a correctly calculated interval, taking into account the time zone offset.
 * The offset will be performed relative to the UTC.
 *
 * @example For data : from `2000-01-01` to `2001-01-01` timeZone `Europe/Moscow`
 *          result: DateTimeRange{
 *             from: new DateTimeImmutable('1999-12-31 21:00:00'),
 *             to: new DateTimeImmutable('2001-01-01 20:59:59'),
 *          }
 */
final readonly class DateRangeDenormalizer
{
    public function __construct(
        private DateDenormalizer $dateDenormalizer,
        private ObjectDenormalizer $objectDenormalizer,
        private TimeZoneDenormalizer $timeZoneDenormalizer,
    ) {
    }

    /**
     * Validates and denormalizes passed data.
     *
     * It expects `$data` to be an array. The array should be of the following scheme:
     *      array{
     *           from: date ISO 8601 ('Y-m-d'),
     *           to: date ISO 8601 ('Y-m-d'),
     *           timeZone: string of timezone (https://www.php.net/manual/en/timezones.php)
     *      }
     *
     * @psalm-param mixed $data Data to validate and denormalize
     * @psalm-param Pointer $pointer Pointer containing path to current field
     *
     * @psalm-return DateTimeRange The DateTimeRange object containing a time interval adjusted for the time zone.
     *
     * @throws ValidationError If `$data` does not meet the requirements of the denormalizer
     */
    public function denormalize(mixed $data, Pointer $pointer): DateTimeRange
    {
        /**
         * @psalm-var array{
         *     from: DateTimeImmutable,
         *     to: DateTimeImmutable,
         *     timeZone: DateTimeZone,
         * } $dateTimeRange
         */
        $dateTimeRange = $this->objectDenormalizer->denormalize($data, $pointer, [
            'from' => new ObjectField(
                fn(mixed $data, Pointer $pointer): DateTimeImmutable => $this->dateDenormalizer->denormalize(
                    $data,
                    $pointer,
                ),
            ),
            'to' => new ObjectField(
                fn(mixed $data, Pointer $pointer): DateTimeImmutable => $this->dateDenormalizer->denormalize(
                    $data,
                    $pointer,
                ),
            ),
            'timeZone' => new ObjectField(
                fn(mixed $data, Pointer $pointer): DateTimeZone => $this->timeZoneDenormalizer->denormalize(
                    $data,
                    $pointer,
                ),
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
    private function getLowerBound(DateTimeImmutable $date, DateTimeZone $timeZone): DateTimeImmutable
    {
        if (0 !== $date->getOffset()) {
            throw new LogicException('Timezone of argument `$date` must be UTC timezone');
        }

        $offsetSeconds = $timeZone->getOffset(new DateTimeImmutable());
        $fromTimestamp = $date->setTime(hour: 0, minute: 0)->getTimestamp() - $offsetSeconds;

        return new DateTimeImmutable()->setTimestamp($fromTimestamp);
    }

    /**
     * @throws ValidationError If `$data` does not meet the requirements of the denormalizer
     */
    private function getUpperBound(DateTimeImmutable $date, DateTimeZone $timeZone): DateTimeImmutable
    {
        if (0 !== $date->getOffset()) {
            throw new LogicException('Timezone of argument `$date` must be UTC timezone');
        }

        $offsetSeconds = $timeZone->getOffset(new DateTimeImmutable());
        $fromTimestamp = $date->setTime(23, 59, 59)->getTimestamp() - $offsetSeconds;

        return new DateTimeImmutable()->setTimestamp($fromTimestamp);
    }
}
