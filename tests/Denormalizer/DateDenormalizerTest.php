<?php

declare(strict_types=1);

namespace Tests\Spiks\UserInputProcessor\Denormalizer;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Spiks\UserInputProcessor\ConstraintViolation\InvalidDate;
use Spiks\UserInputProcessor\Denormalizer\DateDenormalizer;
use Spiks\UserInputProcessor\Denormalizer\StringDenormalizer;
use Spiks\UserInputProcessor\Exception\ValidationError;
use Spiks\UserInputProcessor\Pointer;

/**
 * @covers \Spiks\UserInputProcessor\Denormalizer\DateDenormalizer
 *
 * @internal
 */
final class DateDenormalizerTest extends TestCase
{
    public function testSuccessfulDenormalization(): void
    {
        $dateDenormalizer = new DateDenormalizer(new StringDenormalizer());

        $pointer = Pointer::empty();
        $date = '1996-04-17';
        $processedData = $dateDenormalizer->denormalize($date, $pointer);

        Assert::assertSame($date, $processedData->format('Y-m-d'));
    }

    public function testUnsuccessfulDenormalization(): void
    {
        $dateDenormalizer = new DateDenormalizer(new StringDenormalizer());

        try {
            $pointer = Pointer::empty();
            $dateDenormalizer->denormalize('1996-04-17T22:55:33+00:00', $pointer);
        } catch (ValidationError $exception) {
            Assert::assertCount(1, $exception->getViolations());
            Assert::assertContainsOnly(InvalidDate::class, $exception->getViolations());
        }
    }
}
