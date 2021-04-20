<?php

declare(strict_types=1);

namespace Tests\Flaksp\UserInputProcessor\Denormalizer;

use Flaksp\UserInputProcessor\ConstraintViolation\MandatoryFieldMissing;
use Flaksp\UserInputProcessor\Denormalizer\ObjectDenormalizer;
use Flaksp\UserInputProcessor\Denormalizer\StringDenormalizer;
use Flaksp\UserInputProcessor\Exception\ValidationError;
use Flaksp\UserInputProcessor\ObjectDiscriminatorFields;
use Flaksp\UserInputProcessor\ObjectField;
use Flaksp\UserInputProcessor\ObjectStaticFields;
use Flaksp\UserInputProcessor\Pointer;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Flaksp\UserInputProcessor\Denormalizer\ObjectDenormalizer
 *
 * @internal
 */
final class ObjectDenormalizerTest extends TestCase
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
    public function testSuccessfulDenormalization(
        array $payload
    ): void {
        $objectDenormalizer = new ObjectDenormalizer();
        $stringDenormalizer = new StringDenormalizer();

        $pointer = Pointer::empty();

        $data = $objectDenormalizer->denormalizeStaticFields(
            $payload,
            new ObjectStaticFields([
                'foo' => new ObjectField(
                    static fn ($data) => $stringDenormalizer->denormalize($data, Pointer::append($pointer, 'foo')),
                    isMandatory: true,
                ),
                'bar' => new ObjectField(
                    static fn ($data) => $stringDenormalizer->denormalize($data, Pointer::append($pointer, 'bar'), isNullable: true),
                    isMandatory: true,
                ),
                'baz' => new ObjectField(
                    static fn ($data) => $stringDenormalizer->denormalize($data, Pointer::append($pointer, 'baz')),
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
    public function testSuccessfulDiscriminatorDenormalization(
        array $payload
    ): void {
        $objectDenormalizer = new ObjectDenormalizer();
        $stringDenormalizer = new StringDenormalizer();

        $pointer = Pointer::empty();

        $data = $objectDenormalizer->denormalizeDynamicFields(
            $payload,
            'type',
            new ObjectDiscriminatorFields([
                'a' => new ObjectStaticFields([
                    'foo' => new ObjectField(
                        static fn ($data, $pointer) => $stringDenormalizer->denormalize($data, $pointer),
                        isMandatory: true,
                    ),
                ]),
                'b' => new ObjectStaticFields([
                    'bar' => new ObjectField(
                        static fn ($data, $pointer) => $stringDenormalizer->denormalize($data, $pointer, isNullable: true),
                        isMandatory: true,
                    ),
                    'baz' => new ObjectField(
                        static fn ($data, $pointer) => $stringDenormalizer->denormalize($data, $pointer),
                        isMandatory: false,
                    ),
                ]),
            ]),
            $pointer,
            isNullable: false,
        );

        Assert::assertEquals($payload, $data);
    }

    public function testUnsuccessfulDenormalization(): void
    {
        $objectDenormalizer = new ObjectDenormalizer();
        $stringDenormalizer = new StringDenormalizer();

        $pointer = Pointer::empty();

        try {
            $objectDenormalizer->denormalizeStaticFields(
                [
                    'randomNameField' => '',
                ],
                new ObjectStaticFields([
                    'foo' => new ObjectField(
                        static fn ($data) => $stringDenormalizer->denormalize($data, Pointer::append($pointer, 'foo')),
                        isMandatory: true,
                    ),
                    'bar' => new ObjectField(
                        static fn ($data) => $stringDenormalizer->denormalize($data, Pointer::append($pointer, 'bar'), isNullable: true),
                        isMandatory: true,
                    ),
                    'baz' => new ObjectField(
                        static fn ($data) => $stringDenormalizer->denormalize($data, Pointer::append($pointer, 'baz')),
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
