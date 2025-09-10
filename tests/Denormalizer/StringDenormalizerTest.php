<?php

declare(strict_types=1);

namespace Tests\UserInputProcessor\Denormalizer;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use UserInputProcessor\Denormalizer\StringDenormalizer;
use UserInputProcessor\Pointer;

/**
 * @internal
 */
final class StringDenormalizerTest extends TestCase
{
    #[DataProvider('provideSuccessfulDenormalizationCases')]
    public function testSuccessfulDenormalization(string $payload): void
    {
        $stringDenormalizer = new StringDenormalizer();

        $processedData = $stringDenormalizer->denormalize($payload, Pointer::empty(), minLength: 1);

        $this->assertSame($payload, $processedData);
    }

    /**
     * @psalm-return string[][]
     */
    public static function provideSuccessfulDenormalizationCases(): iterable
    {
        return [['foobar'], ['ФуБар'], ['`'], ['!@#$%^&*(']];
    }
}
