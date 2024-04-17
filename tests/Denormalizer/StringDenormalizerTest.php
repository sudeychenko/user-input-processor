<?php

declare(strict_types=1);

namespace Tests\Spiks\UserInputProcessor\Denormalizer;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Spiks\UserInputProcessor\Denormalizer\StringDenormalizer;
use Spiks\UserInputProcessor\Pointer;

/**
 * @covers \Spiks\UserInputProcessor\StringDenormalizer
 *
 * @internal
 */
final class StringDenormalizerTest extends TestCase
{
    /**
     * @return string[][]
     */
    public static function provideSuccessfulDenormalizationCases(): iterable
    {
        return [['foobar']];
    }

    /**
     * @dataProvider provideSuccessfulDenormalizationCases
     */
    public function testSuccessfulDenormalization(string $payload): void
    {
        $stringDenormalizer = new StringDenormalizer();

        $processedData = $stringDenormalizer->denormalize($payload, Pointer::empty(), minLength: 1);

        Assert::assertEquals($payload, $processedData);
    }
}
