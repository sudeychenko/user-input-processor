<?php

declare(strict_types=1);

namespace Tests\Flaksp\UserInputProcessor\Deserializer;

use Flaksp\UserInputProcessor\Deserializer\StringDeserializer;
use Flaksp\UserInputProcessor\JsonPointer;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Flaksp\UserInputProcessor\StringDeserializer
 *
 * @internal
 */
final class StringDeserializerTest extends TestCase
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
    public function testSuccessfulDeserialization(
        string $payload
    ): void {
        $stringDeserializer = new StringDeserializer();

        $data = $stringDeserializer->deserialize(
            $payload,
            JsonPointer::empty(),
            isNullable: false,
            minLength: 1,
        );

        Assert::assertIsString($data);
    }
}
