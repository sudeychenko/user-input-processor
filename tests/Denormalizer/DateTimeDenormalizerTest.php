<?php

declare(strict_types=1);

namespace Tests\Spiks\UserInputProcessor\Denormalizer;

use DateTimeImmutable;
use DateTimeInterface;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Spiks\UserInputProcessor\ConstraintViolation\InvalidDate;
use Spiks\UserInputProcessor\ConstraintViolation\WrongPropertyType;
use Spiks\UserInputProcessor\DateTimeRange;
use Spiks\UserInputProcessor\Denormalizer\DateTimeDenormalizer;
use Spiks\UserInputProcessor\Denormalizer\ObjectDenormalizer;
use Spiks\UserInputProcessor\Denormalizer\StringDenormalizer;
use Spiks\UserInputProcessor\Denormalizer\TimeZoneDenormalizer;
use Spiks\UserInputProcessor\Exception\ValidationError;
use Spiks\UserInputProcessor\Pointer;

/**
 * @covers \Spiks\UserInputProcessor\Denormalizer\ObjectDenormalizer
 *
 * @internal
 */
final class DateTimeDenormalizerTest extends TestCase
{
    /**
     * @psalm-return list<array{
     *     from: non-empty-string,
     *     timeZone: non-empty-string,
     *     to: non-empty-string,
     *     expectedDateTimeRange: DateTimeRange,
     * }>
     */
    public static function provideSuccessfulDenormalizationDateRangeWithDateTimeZoneCases(): iterable
    {
        return [
            [
                'from' => '2022-01-01',
                'timeZone' => 'Europe/Moscow',
                'to' => '2022-01-03',
                'expectedDateTimeRange' => new DateTimeRange(
                    from: new DateTimeImmutable('2021-12-31 21:00:00'),
                    to: new DateTimeImmutable('2022-01-03 20:59:59'),
                ),
            ],
            [
                'from' => '2021-12-31',
                'timeZone' => 'America/Barbados',
                'to' => '2022-01-03',
                'expectedDateTimeRange' => new DateTimeRange(
                    from: new DateTimeImmutable('2021-12-31 4:00:00'),
                    to: new DateTimeImmutable('2022-01-04 03:59:59'),
                ),
            ],
            [
                'from' => '2022-01-01',
                'timeZone' => 'Asia/Singapore',
                'to' => '2022-01-03',
                'expectedDateTimeRange' => new DateTimeRange(
                    from: new DateTimeImmutable('2021-12-31 16:00:00'),
                    to: new DateTimeImmutable('2022-01-03 15:59:59'),
                ),
            ],
        ];
    }

    public function testSuccessfulDenormalization(): void
    {
        $stringDenormalizer = new StringDenormalizer();
        $objectDenormalizer = new ObjectDenormalizer();
        $timeZoneDenormalizer = new TimeZoneDenormalizer($stringDenormalizer);
        $dateTimeDenormalizer = new DateTimeDenormalizer($stringDenormalizer, $objectDenormalizer, $timeZoneDenormalizer);

        $pointer = Pointer::empty();
        $date = '1996-04-17T22:55:33+00:00';
        $processedData = $dateTimeDenormalizer->denormalize($date, $pointer);

        Assert::assertSame($date, $processedData->format(DateTimeInterface::RFC3339));
    }

    public function testSuccessfulDenormalizationDateOnly(): void
    {
        $stringDenormalizer = new StringDenormalizer();
        $objectDenormalizer = new ObjectDenormalizer();
        $timeZoneDenormalizer = new TimeZoneDenormalizer($stringDenormalizer);
        $dateTimeDenormalizer = new DateTimeDenormalizer($stringDenormalizer, $objectDenormalizer, $timeZoneDenormalizer);

        $pointer = Pointer::empty();
        $date = '1996-04-17';
        $processedData = $dateTimeDenormalizer->denormalizeDateOnly($date, $pointer);

        Assert::assertSame($date, $processedData->format('Y-m-d'));
    }

    /**
     * @param non-empty-string $form
     * @param non-empty-string $timeZone
     * @param non-empty-string $to
     *
     * @dataProvider provideSuccessfulDenormalizationDateRangeWithDateTimeZoneCases
     */
    public function testSuccessfulDenormalizationDateRangeWithDateTimeZone(
        string $form,
        string $timeZone,
        string $to,
        DateTimeRange $expectedDateTimeRange,
    ): void {
        $stringDenormalizer = new StringDenormalizer();
        $objectDenormalizer = new ObjectDenormalizer();
        $timeZoneDenormalizer = new TimeZoneDenormalizer(stringDenormalizer: $stringDenormalizer);
        $dateTimeDenormalizer = new DateTimeDenormalizer(
            stringDenormalizer: $stringDenormalizer,
            objectDenormalizer: $objectDenormalizer,
            timeZoneDenormalizer: $timeZoneDenormalizer
        );

        $dateTimeRange = $dateTimeDenormalizer->denormalizeDateRangeWithDateTimeZone(
            data: [
                'from' => $form,
                'timeZone' => $timeZone,
                'to' => $to,
            ],
            pointer: Pointer::empty(),
        );

        Assert::assertSame(
            expected: $expectedDateTimeRange->from->getTimestamp(),
            actual: $dateTimeRange->from->getTimestamp(),
        );

        Assert::assertSame(
            expected: $expectedDateTimeRange->to->getTimestamp(),
            actual: $dateTimeRange->to->getTimestamp(),
        );
    }

    public function testUnsuccessfulDenormalization(): void
    {
        $stringDenormalizer = new StringDenormalizer();
        $objectDenormalizer = new ObjectDenormalizer();
        $timeZoneDenormalizer = new TimeZoneDenormalizer(stringDenormalizer: $stringDenormalizer);
        $dateTimeDenormalizer = new DateTimeDenormalizer(
            stringDenormalizer: $stringDenormalizer,
            objectDenormalizer: $objectDenormalizer,
            timeZoneDenormalizer: $timeZoneDenormalizer
        );

        $pointer = Pointer::empty();

        try {
            $dateTimeDenormalizer->denormalize('2003-13-32T22:55:33+00:00', $pointer);
        } catch (ValidationError $exception) {
            Assert::assertCount(1, $exception->getViolations());
            Assert::assertContainsOnly(InvalidDate::class, $exception->getViolations());
        }
    }

    public function testUnsuccessfulDenormalizationDateOnly(): void
    {
        $stringDenormalizer = new StringDenormalizer();
        $objectDenormalizer = new ObjectDenormalizer();
        $timeZoneDenormalizer = new TimeZoneDenormalizer(stringDenormalizer: $stringDenormalizer);
        $dateTimeDenormalizer = new DateTimeDenormalizer(
            stringDenormalizer: $stringDenormalizer,
            objectDenormalizer: $objectDenormalizer,
            timeZoneDenormalizer: $timeZoneDenormalizer
        );

        try {
            $pointer = Pointer::empty();
            $dateTimeDenormalizer->denormalizeDateOnly('1996-04-17T22:55:33+00:00', $pointer);
        } catch (ValidationError $exception) {
            Assert::assertCount(1, $exception->getViolations());
            Assert::assertContainsOnly(InvalidDate::class, $exception->getViolations());
        }
    }

    public function testUnsuccessfulDenormalizationDateRangeWithDateTimeZone(): void
    {
        $stringDenormalizer = new StringDenormalizer();
        $objectDenormalizer = new ObjectDenormalizer();
        $timeZoneDenormalizer = new TimeZoneDenormalizer(stringDenormalizer: $stringDenormalizer);
        $dateTimeDenormalizer = new DateTimeDenormalizer(
            stringDenormalizer: $stringDenormalizer,
            objectDenormalizer: $objectDenormalizer,
            timeZoneDenormalizer: $timeZoneDenormalizer
        );
        $pointer = Pointer::empty();

        try {
            $dateTimeDenormalizer->denormalizeDateRangeWithDateTimeZone([], $pointer);
        } catch (ValidationError $exception) {
            Assert::assertCount(1, $exception->getViolations());
            Assert::assertContainsOnly(WrongPropertyType::class, $exception->getViolations());
        }

        try {
            $dateTimeDenormalizer->denormalizeDateRangeWithDateTimeZone(
                data: [
                    'from' => '2019-01-01',
                    'timeZone' => 'Asia/Singapore',
                    'to' => '2022-01-01 00:00:00+00:00',
                ],
                pointer: Pointer::empty(),
            );
        } catch (ValidationError $exception) {
            Assert::assertCount(1, $exception->getViolations());
            Assert::assertContainsOnly(InvalidDate::class, $exception->getViolations());
        }
    }
}
