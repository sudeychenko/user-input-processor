<?php

declare(strict_types=1);

namespace Tests\Spiks\UserInputProcessor\Denormalizer;

use DateTimeInterface;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Spiks\UserInputProcessor\ConstraintViolation\InvalidDate;
use Spiks\UserInputProcessor\Denormalizer\DateTimeDenormalizer;
use Spiks\UserInputProcessor\Denormalizer\StringDenormalizer;
use Spiks\UserInputProcessor\Exception\ValidationError;
use Spiks\UserInputProcessor\Pointer;

/**
 * @covers \Spiks\UserInputProcessor\Denormalizer\DateTimeDenormalizer
 *
 * @internal
 */
final class DateTimeDenormalizerTest extends TestCase
{
    public function testSuccessfulDenormalization(): void
    {
        $dateTimeDenormalizer = new DateTimeDenormalizer(new StringDenormalizer());

        $pointer = Pointer::empty();
        $date = '1996-04-17T22:55:33+00:00';

        $processedData = $dateTimeDenormalizer->denormalize($date, $pointer);

        Assert::assertSame($date, $processedData->format(DateTimeInterface::RFC3339));
    }

    public function testUnsuccessfulDenormalization(): void
    {
        $dateTimeDenormalizer = new DateTimeDenormalizer(new StringDenormalizer());
        $pointer = Pointer::empty();

        try {
            $dateTimeDenormalizer->denormalize('2003-13-32T22:55:33+00:00', $pointer);
        } catch (ValidationError $exception) {
            Assert::assertCount(1, $exception->getViolations());
            Assert::assertContainsOnly(InvalidDate::class, $exception->getViolations());
        }

        try {
            $dateTimeDenormalizer->denormalize('2000-01-01 22:55:33', $pointer);
        } catch (ValidationError $exception) {
            Assert::assertCount(1, $exception->getViolations());
            Assert::assertContainsOnly(InvalidDate::class, $exception->getViolations());
        }
    }
}
