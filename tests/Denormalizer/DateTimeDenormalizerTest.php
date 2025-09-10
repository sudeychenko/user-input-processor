<?php

declare(strict_types=1);

namespace Tests\UserInputProcessor\Denormalizer;

use DateTimeInterface;
use PHPUnit\Framework\TestCase;
use UserInputProcessor\ConstraintViolation\InvalidDate;
use UserInputProcessor\Denormalizer\DateTimeDenormalizer;
use UserInputProcessor\Denormalizer\StringDenormalizer;
use UserInputProcessor\Exception\ValidationError;
use UserInputProcessor\Pointer;

/**
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

        $this->assertSame($date, $processedData->format(DateTimeInterface::RFC3339));
    }

    public function testUnsuccessfulDenormalization(): void
    {
        $dateTimeDenormalizer = new DateTimeDenormalizer(new StringDenormalizer());
        $pointer = Pointer::empty();

        try {
            $dateTimeDenormalizer->denormalize('2003-13-32T22:55:33+00:00', $pointer);
        } catch (ValidationError $exception) {
            $this->assertCount(1, $exception->getViolations());
            $validationErrors = $exception->getViolations();
            $this->assertInstanceOf(InvalidDate::class, array_pop($validationErrors));
        }

        try {
            $dateTimeDenormalizer->denormalize('2000-01-01 22:55:33', $pointer);
        } catch (ValidationError $exception) {
            $this->assertCount(1, $exception->getViolations());
            $validationErrors = $exception->getViolations();
            $this->assertInstanceOf(InvalidDate::class, array_pop($validationErrors));
        }
    }
}
