<?php

declare(strict_types=1);

namespace Tests\Flaksp\UserInputDeserializer\Deserializer;

use Flaksp\UserInputDeserializer\ConstraintViolation\MandatoryFieldMissing;
use Flaksp\UserInputDeserializer\Deserializer\ObjectDeserializer;
use Flaksp\UserInputDeserializer\Deserializer\StringDeserializer;
use Flaksp\UserInputDeserializer\Exception\ValidationError;
use Flaksp\UserInputDeserializer\JsonPointer;
use Flaksp\UserInputDeserializer\ObjectDiscriminatorFields;
use Flaksp\UserInputDeserializer\ObjectField;
use Flaksp\UserInputDeserializer\ObjectStaticFields;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Flaksp\UserInputDeserializer\Deserializer\ObjectDeserializer
 *
 * @internal
 */
final class ObjectDeserializerTest extends TestCase
{
    public function dynamicFieldsDataProvider(): array
    {
        return [
            [
                [
                    'type' => 'a',
                    'foo' => 'Test',
                ],
            ],
            [
                [
                    'type' => 'b',
                    'bar' => 'Test',
                    'baz' => 'Test',
                ],
            ],
        ];
    }

    public function staticFieldsDataProvider(): array
    {
        return [
            [
                [
                    'foo' => 'Test',
                    'bar' => 'Test',
                    'baz' => 'Test',
                ],
            ],
            [
                [
                    'foo' => 'Test',
                    'bar' => null,
                ],
            ],
        ];
    }

    /**
     * @dataProvider staticFieldsDataProvider
     */
    public function testSuccessfulDeserialization(
        array $payload
    ): void {
        $objectDeserializer = new ObjectDeserializer();
        $stringDeserializer = new StringDeserializer();

        $pointer = JsonPointer::empty();

        $data = $objectDeserializer->deserializeStaticFields(
            $payload,
            new ObjectStaticFields([
                'foo' => new ObjectField(
                    static fn ($data) => $stringDeserializer->deserialize($data, JsonPointer::append($pointer, 'foo')),
                    isMandatory: true,
                ),
                'bar' => new ObjectField(
                    static fn ($data) => $stringDeserializer->deserialize($data, JsonPointer::append($pointer, 'bar'), isNullable: true),
                    isMandatory: true,
                ),
                'baz' => new ObjectField(
                    static fn ($data) => $stringDeserializer->deserialize($data, JsonPointer::append($pointer, 'baz')),
                    isMandatory: false,
                ),
            ]),
            $pointer,
            isNullable: false,
        );

        Assert::assertEquals($payload, $data);
    }

    /**
     * @dataProvider dynamicFieldsDataProvider
     */
    public function testSuccessfulDiscriminatorDeserialization(
        array $payload
    ): void {
        $objectDeserializer = new ObjectDeserializer();
        $stringDeserializer = new StringDeserializer();

        $pointer = JsonPointer::empty();

        $data = $objectDeserializer->deserializeDynamicFields(
            $payload,
            'type',
            new ObjectDiscriminatorFields([
                'a' => new ObjectStaticFields([
                    'foo' => new ObjectField(
                        static fn ($data, $pointer) => $stringDeserializer->deserialize($data, $pointer),
                        isMandatory: true,
                    ),
                ]),
                'b' => new ObjectStaticFields([
                    'bar' => new ObjectField(
                        static fn ($data, $pointer) => $stringDeserializer->deserialize($data, $pointer, isNullable: true),
                        isMandatory: true,
                    ),
                    'baz' => new ObjectField(
                        static fn ($data, $pointer) => $stringDeserializer->deserialize($data, $pointer),
                        isMandatory: false,
                    ),
                ]),
            ]),
            $pointer,
            isNullable: false,
        );

        Assert::assertEquals($payload, $data);
    }

    public function testUnsuccessfulDeserialization(): void
    {
        $objectDeserializer = new ObjectDeserializer();
        $stringDeserializer = new StringDeserializer();

        $pointer = JsonPointer::empty();

        try {
            $objectDeserializer->deserializeStaticFields(
                [
                    'randomNameField' => '',
                ],
                new ObjectStaticFields([
                    'foo' => new ObjectField(
                        static fn ($data) => $stringDeserializer->deserialize($data, JsonPointer::append($pointer, 'foo')),
                        isMandatory: true,
                    ),
                    'bar' => new ObjectField(
                        static fn ($data) => $stringDeserializer->deserialize($data, JsonPointer::append($pointer, 'bar'), isNullable: true),
                        isMandatory: true,
                    ),
                    'baz' => new ObjectField(
                        static fn ($data) => $stringDeserializer->deserialize($data, JsonPointer::append($pointer, 'baz')),
                        isMandatory: false,
                    ),
                ]),
                $pointer,
                isNullable: false,
            );
        } catch (ValidationError $exception) {
            Assert::assertCount(2, $exception->getViolations());
            Assert::assertContainsOnly(MandatoryFieldMissing::class, $exception->getViolations());
        }
    }
}
