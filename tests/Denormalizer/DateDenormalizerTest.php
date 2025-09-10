<?php

declare(strict_types=1);

namespace Tests\UserInputProcessor\Denormalizer;

use PHPUnit\Framework\TestCase;
use UserInputProcessor\ConstraintViolation\InvalidDate;
use UserInputProcessor\Denormalizer\DateDenormalizer;
use UserInputProcessor\Denormalizer\StringDenormalizer;
use UserInputProcessor\Exception\ValidationError;
use UserInputProcessor\Pointer;

/**
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

        $this->assertSame($date, $processedData->format('Y-m-d'));
    }

    public function testUnsuccessfulDenormalization(): void
    {
        $dateDenormalizer = new DateDenormalizer(new StringDenormalizer());

        try {
            $pointer = Pointer::empty();
            $dateDenormalizer->denormalize('1996-04-17T22:55:33+00:00', $pointer);
        } catch (ValidationError $exception) {
            $this->assertCount(1, $exception->getViolations());
            $validationErrors = $exception->getViolations();
            $this->assertInstanceOf(InvalidDate::class, array_pop($validationErrors));
        }
    }
}
