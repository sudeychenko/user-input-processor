<?php

declare(strict_types=1);

namespace Tests\UserInputProcessor\Denormalizer;

use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use UserInputProcessor\ConstraintViolation\InvalidDate;
use UserInputProcessor\ConstraintViolation\InvalidDateRange;
use UserInputProcessor\ConstraintViolation\WrongPropertyType;
use UserInputProcessor\DateTimeRange;
use UserInputProcessor\Denormalizer\DateDenormalizer;
use UserInputProcessor\Denormalizer\DateRangeDenormalizer;
use UserInputProcessor\Denormalizer\ObjectDenormalizer;
use UserInputProcessor\Denormalizer\StringDenormalizer;
use UserInputProcessor\Denormalizer\TimeZoneDenormalizer;
use UserInputProcessor\Exception\ValidationError;
use UserInputProcessor\Pointer;

/**
 * @internal
 */
final class DateRangeDenormalizerTest extends TestCase
{
    /**
     * @psalm-param non-empty-string $from
     * @psalm-param non-empty-string $timeZone
     * @psalm-param non-empty-string $to
     */
    #[DataProvider('provideSuccessfulDenormalizationCases')]
    public function testSuccessfulDenormalization(
        string $from,
        string $to,
        string $timeZone,
        DateTimeRange $expectedDateTimeRange,
    ): void {
        $dateRangeDenormalizer = $this->getDenormalizer();

        $dateTimeRange = $dateRangeDenormalizer->denormalize(
            data: [
                'from' => $from,
                'to' => $to,
                'timeZone' => $timeZone,
            ],
            pointer: Pointer::empty(),
        );

        $this->assertSame(
            expected: $expectedDateTimeRange->from->getTimestamp(),
            actual: $dateTimeRange->from->getTimestamp(),
        );

        $this->assertSame(
            expected: $expectedDateTimeRange->to->getTimestamp(),
            actual: $dateTimeRange->to->getTimestamp(),
        );
    }

    /**
     * @psalm-return list<array{
     *     from: non-empty-string,
     *     to: non-empty-string,
     *     timeZone: non-empty-string,
     *     expectedDateTimeRange: DateTimeRange,
     * }>
     */
    public static function provideSuccessfulDenormalizationCases(): iterable
    {
        return [
            [
                'from' => '2000-01-01',
                'to' => '2000-12-31',
                'timeZone' => 'Europe/Moscow', // GMT+3
                'expectedDateTimeRange' => new DateTimeRange(
                    from: new DateTimeImmutable('1999-12-31 21:00:00'),
                    to: new DateTimeImmutable('2000-12-31 20:59:59'),
                ),
            ],
            [
                'from' => '2000-01-01',
                'to' => '2000-12-31',
                'timeZone' => 'America/Barbados', // GMT-4
                'expectedDateTimeRange' => new DateTimeRange(
                    from: new DateTimeImmutable('2000-01-01 04:00:00'),
                    to: new DateTimeImmutable('2001-01-01 03:59:59'),
                ),
            ],
            [
                'from' => '2000-01-01',
                'to' => '2000-12-31',
                'timeZone' => 'Asia/Singapore', // GMT+8
                'expectedDateTimeRange' => new DateTimeRange(
                    from: new DateTimeImmutable('1999-12-31 16:00:00'),
                    to: new DateTimeImmutable('2000-12-31 15:59:59'),
                ),
            ],
        ];
    }

    public function testUnsuccessfulDenormalization(): void
    {
        $dateRangeDenormalizer = $this->getDenormalizer();
        $pointer = Pointer::empty();

        try {
            $dateRangeDenormalizer->denormalize([], $pointer);
        } catch (ValidationError $exception) {
            $this->assertCount(1, $exception->getViolations());
            $validationErrors = $exception->getViolations();
            $this->assertInstanceOf(WrongPropertyType::class, array_pop($validationErrors));
        }

        try {
            $dateRangeDenormalizer->denormalize(
                data: [
                    'from' => '2010-01-01',
                    'timeZone' => 'Asia/Singapore',
                    'to' => '2000-01-01',
                ],
                pointer: Pointer::empty(),
            );
        } catch (ValidationError $exception) {
            $this->assertCount(1, $exception->getViolations());
            $validationErrors = $exception->getViolations();
            $this->assertInstanceOf(InvalidDateRange::class, array_pop($validationErrors));
        }

        try {
            $dateRangeDenormalizer->denormalize(
                data: [
                    'from' => '2019-01-01',
                    'timeZone' => 'Asia/Singapore',
                    'to' => '2022-01-01 00:00:00+00:00',
                ],
                pointer: Pointer::empty(),
            );
        } catch (ValidationError $exception) {
            $this->assertCount(1, $exception->getViolations());
            $validationErrors = $exception->getViolations();
            $this->assertInstanceOf(InvalidDate::class, array_pop($validationErrors));
        }
    }

    private function getDenormalizer(): DateRangeDenormalizer
    {
        $stringDenormalizer = new StringDenormalizer();

        return new DateRangeDenormalizer(
            dateDenormalizer: new DateDenormalizer($stringDenormalizer),
            objectDenormalizer: new ObjectDenormalizer(),
            timeZoneDenormalizer: new TimeZoneDenormalizer($stringDenormalizer),
        );
    }
}
