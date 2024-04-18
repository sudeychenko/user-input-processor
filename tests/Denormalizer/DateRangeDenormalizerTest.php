<?php

declare(strict_types=1);

namespace Tests\Spiks\UserInputProcessor\Denormalizer;

use DateTimeImmutable;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Spiks\UserInputProcessor\ConstraintViolation\InvalidDate;
use Spiks\UserInputProcessor\ConstraintViolation\InvalidDateRange;
use Spiks\UserInputProcessor\ConstraintViolation\WrongPropertyType;
use Spiks\UserInputProcessor\DateTimeRange;
use Spiks\UserInputProcessor\Denormalizer\DateDenormalizer;
use Spiks\UserInputProcessor\Denormalizer\DateRangeDenormalizer;
use Spiks\UserInputProcessor\Denormalizer\ObjectDenormalizer;
use Spiks\UserInputProcessor\Denormalizer\StringDenormalizer;
use Spiks\UserInputProcessor\Denormalizer\TimeZoneDenormalizer;
use Spiks\UserInputProcessor\Exception\ValidationError;
use Spiks\UserInputProcessor\Pointer;

/**
 * @covers \Spiks\UserInputProcessor\Denormalizer\DateRangeDenormalizer
 *
 * @internal
 */
final class DateRangeDenormalizerTest extends TestCase
{
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
                    to: new DateTimeImmutable('2000-12-31 20:59:59')
                ),
            ],
            [
                'from' => '2000-01-01',
                'to' => '2000-12-31',
                'timeZone' => 'America/Barbados', // GMT-4
                'expectedDateTimeRange' => new DateTimeRange(
                    from: new DateTimeImmutable('2000-01-01 04:00:00'),
                    to: new DateTimeImmutable('2001-01-01 03:59:59')
                ),
            ],
            [
                'from' => '2000-01-01',
                'to' => '2000-12-31',
                'timeZone' => 'Asia/Singapore', // GMT+8
                'expectedDateTimeRange' => new DateTimeRange(
                    from: new DateTimeImmutable('1999-12-31 16:00:00'),
                    to: new DateTimeImmutable('2000-12-31 15:59:59')
                ),
            ],
        ];
    }

    /**
     * @param non-empty-string $form
     * @param non-empty-string $timeZone
     * @param non-empty-string $to
     *
     * @dataProvider provideSuccessfulDenormalizationCases
     */
    public function testSuccessfulDenormalization(
        string $form,
        string $to,
        string $timeZone,
        DateTimeRange $expectedDateTimeRange
    ): void {
        $dateRangeDenormalizer = $this->getDenormalizer();

        $dateTimeRange = $dateRangeDenormalizer->denormalize(
            data: [
                'from' => $form,
                'to' => $to,
                'timeZone' => $timeZone,
            ],
            pointer: Pointer::empty()
        );

        Assert::assertSame(
            expected: $expectedDateTimeRange->from->getTimestamp(),
            actual: $dateTimeRange->from->getTimestamp()
        );

        Assert::assertSame(
            expected: $expectedDateTimeRange->to->getTimestamp(),
            actual: $dateTimeRange->to->getTimestamp()
        );
    }

    public function testUnsuccessfulDenormalization(): void
    {
        $dateRangeDenormalizer = $this->getDenormalizer();
        $pointer = Pointer::empty();

        try {
            $dateRangeDenormalizer->denormalize([], $pointer);
        } catch (ValidationError $exception) {
            Assert::assertCount(1, $exception->getViolations());
            Assert::assertContainsOnly(WrongPropertyType::class, $exception->getViolations());
        }

        try {
            $dateRangeDenormalizer->denormalize(
                data: [
                    'from' => '2010-01-01',
                    'timeZone' => 'Asia/Singapore',
                    'to' => '2000-01-01',
                ],
                pointer: Pointer::empty()
            );
        } catch (ValidationError $exception) {
            Assert::assertCount(1, $exception->getViolations());
            Assert::assertContainsOnly(InvalidDateRange::class, $exception->getViolations());
        }

        try {
            $dateRangeDenormalizer->denormalize(
                data: [
                    'from' => '2019-01-01',
                    'timeZone' => 'Asia/Singapore',
                    'to' => '2022-01-01 00:00:00+00:00',
                ],
                pointer: Pointer::empty()
            );
        } catch (ValidationError $exception) {
            Assert::assertCount(1, $exception->getViolations());
            Assert::assertContainsOnly(InvalidDate::class, $exception->getViolations());
        }
    }

    private function getDenormalizer(): DateRangeDenormalizer
    {
        $stringDenormalizer = new StringDenormalizer();

        return new DateRangeDenormalizer(
            dateDenormalizer: new DateDenormalizer($stringDenormalizer),
            objectDenormalizer: new ObjectDenormalizer(),
            timeZoneDenormalizer: new TimeZoneDenormalizer($stringDenormalizer)
        );
    }
}
