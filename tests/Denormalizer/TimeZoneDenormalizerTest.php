<?php

declare(strict_types=1);

namespace Tests\UserInputProcessor\Denormalizer;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use UserInputProcessor\ConstraintViolation\InvalidTimeZone;
use UserInputProcessor\Denormalizer\StringDenormalizer;
use UserInputProcessor\Denormalizer\TimeZoneDenormalizer;
use UserInputProcessor\Exception\ValidationError;
use UserInputProcessor\Pointer;

/**
 * @internal
 */
final class TimeZoneDenormalizerTest extends TestCase
{
    #[DataProvider('provideSuccessfulDenormalizationCases')]
    public function testSuccessfulDenormalization(string $timeZone): void
    {
        $timeZoneDenormalizer = new TimeZoneDenormalizer(new StringDenormalizer());
        $pointer = Pointer::empty();

        $processedData = $timeZoneDenormalizer->denormalize($timeZone, $pointer);

        $this->assertSame($timeZone, $processedData->getName());
    }

    /**
     * @psalm-return list<array{non-empty-string}>
     */
    public static function provideSuccessfulDenormalizationCases(): iterable
    {
        return [['Asia/Singapore'], ['Europe/Moscow'], ['America/Barbados'], ['+03:00']];
    }

    public function testUnsuccessfulDenormalization(): void
    {
        $timeZoneDenormalizer = new TimeZoneDenormalizer(new StringDenormalizer());

        try {
            $timeZoneDenormalizer->denormalize('foo/bar', Pointer::empty());
        } catch (ValidationError $exception) {
            $this->assertCount(1, $exception->getViolations());
            $validationErrors = $exception->getViolations();
            $this->assertInstanceOf(InvalidTimeZone::class, array_pop($validationErrors));
        }
    }
}
