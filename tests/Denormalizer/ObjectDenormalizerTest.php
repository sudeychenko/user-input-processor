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

        $processedData = $objectDenormalizer->denormalizeStaticFields(
            $payload,
            $pointer,
            new ObjectStaticFields([
                'foo' => new ObjectField(
                    static fn (mixed $fieldData, Pointer $fieldPointer) => $stringDenormalizer->denormalize($fieldData, $fieldPointer),
                    isMandatory: true,
                ),
                'bar' => new ObjectField(
                    static fn (mixed $fieldData, Pointer $fieldPointer) => $stringDenormalizer->denormalize($fieldData, $fieldPointer),
                    isMandatory: true,
                    isNullable: true,
                ),
                'baz' => new ObjectField(
                    static fn (mixed $fieldData, Pointer $fieldPointer) => $stringDenormalizer->denormalize($fieldData, $fieldPointer),
                    isMandatory: false,
                ),
            ]),
        );

        Assert::assertEquals($payload, $processedData);
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

        $processedData = $objectDenormalizer->denormalizeDynamicFields(
            $payload,
            $pointer,
            'type',
            new ObjectDiscriminatorFields([
                'a' => new ObjectStaticFields([
                    'foo' => new ObjectField(
                        static fn (mixed $fieldData, Pointer $fieldPointer) => $stringDenormalizer->denormalize($fieldData, $fieldPointer),
                        isMandatory: true,
                    ),
                ]),
                'b' => new ObjectStaticFields([
                    'bar' => new ObjectField(
                        static fn (mixed $fieldData, Pointer $fieldPointer) => $stringDenormalizer->denormalize($fieldData, $fieldPointer),
                        isMandatory: true,
                        isNullable: true,
                    ),
                    'baz' => new ObjectField(
                        static fn (mixed $fieldData, Pointer $fieldPointer) => $stringDenormalizer->denormalize($fieldData, $fieldPointer),
                        isMandatory: false,
                    ),
                ]),
            ]),
        );

        Assert::assertEquals($payload, $processedData);
    }

    public function testUnsuccessfulDenormalization(): void
    {
        $objectDenormalizer = new ObjectDenormalizer();
        $stringDenormalizer = new StringDenormalizer();

        $data = [
            'randomNameField' => '',
        ];

        $pointer = Pointer::empty();

        try {
            $objectDenormalizer->denormalizeStaticFields(
                $data,
                $pointer,
                new ObjectStaticFields([
                    'foo' => new ObjectField(
                        static fn (mixed $fieldData, Pointer $fieldPointer) => $stringDenormalizer->denormalize($fieldData, $fieldPointer),
                        isMandatory: true,
                    ),
                    'bar' => new ObjectField(
                        static fn (mixed $fieldData, Pointer $fieldPointer) => $stringDenormalizer->denormalize($fieldData, $fieldPointer),
                        isMandatory: true,
                        isNullable: true,
                    ),
                    'baz' => new ObjectField(
                        static fn (mixed $fieldData, Pointer $fieldPointer) => $stringDenormalizer->denormalize($fieldData, $fieldPointer),
                        isMandatory: false,
                    ),
                ]),
            );
        } catch (ValidationError $exception) {
            Assert::assertCount(2, $exception->getViolations());
            Assert::assertContainsOnly(MandatoryFieldMissing::class, $exception->getViolations());
        }
    }
}
