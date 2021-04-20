<?php

declare(strict_types=1);

namespace Tests\Flaksp\UserInputProcessor\Denormalizer;

use Flaksp\UserInputProcessor\Denormalizer\StringDenormalizer;
use Flaksp\UserInputProcessor\Pointer;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Flaksp\UserInputProcessor\StringDenormalizer
 *
 * @internal
 */
final class StringDenormalizerTest extends TestCase
{
    public function successfulScenarioDataProvider(): array
    {
        return [
            [
                'foobar',
            ],
        ];
    }

    /**
     * @dataProvider successfulScenarioDataProvider
     */
    public function testSuccessfulDenormalization(
        string $payload
    ): void {
        $stringDenormalizer = new StringDenormalizer();

        $data = $stringDenormalizer->denormalize(
            $payload,
            Pointer::empty(),
            isNullable: false,
            minLength: 1,
        );

        Assert::assertIsString($data);
    }
}
