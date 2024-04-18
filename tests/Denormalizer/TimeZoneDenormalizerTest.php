<?php

declare(strict_types=1);

namespace Tests\Spiks\UserInputProcessor\Denormalizer;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Spiks\UserInputProcessor\ConstraintViolation\InvalidTimeZone;
use Spiks\UserInputProcessor\Denormalizer\StringDenormalizer;
use Spiks\UserInputProcessor\Denormalizer\TimeZoneDenormalizer;
use Spiks\UserInputProcessor\Exception\ValidationError;
use Spiks\UserInputProcessor\Pointer;

/**
 * @covers \Spiks\UserInputProcessor\Denormalizer\TimeZoneDenormalizer
 *
 * @internal
 */
final class TimeZoneDenormalizerTest extends TestCase
{
    /**
     * @psalm-return list<array{non-empty-string}>
     */
    public static function provideSuccessfulDenormalizationCases(): iterable
    {
        return [['Asia/Singapore'], ['Europe/Moscow'], ['America/Barbados'], ['+03:00']];
    }

    /**
     * @dataProvider provideSuccessfulDenormalizationCases
     */
    public function testSuccessfulDenormalization(string $timeZone): void
    {
        $timeZoneDenormalizer = new TimeZoneDenormalizer(new StringDenormalizer());
        $pointer = Pointer::empty();

        $processedData = $timeZoneDenormalizer->denormalize($timeZone, $pointer);

        Assert::assertSame($timeZone, $processedData->getName());
    }

    public function testUnsuccessfulDenormalization(): void
    {
        $timeZoneDenormalizer = new TimeZoneDenormalizer(new StringDenormalizer());

        try {
            $timeZoneDenormalizer->denormalize('foo/bar', Pointer::empty());
        } catch (ValidationError $exception) {
            Assert::assertCount(1, $exception->getViolations());
            Assert::assertContainsOnly(InvalidTimeZone::class, $exception->getViolations());
        }
    }
}
