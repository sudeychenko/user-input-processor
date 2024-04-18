<?php

declare(strict_types=1);

namespace Tests\Spiks\UserInputProcessor\Denormalizer;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Spiks\UserInputProcessor\ConstraintViolation\MandatoryFieldMissing;
use Spiks\UserInputProcessor\Denormalizer\ObjectDenormalizer;
use Spiks\UserInputProcessor\Denormalizer\StringDenormalizer;
use Spiks\UserInputProcessor\Exception\ValidationError;
use Spiks\UserInputProcessor\ObjectField;
use Spiks\UserInputProcessor\Pointer;

/**
 * @covers \Spiks\UserInputProcessor\Denormalizer\ObjectDenormalizer
 *
 * @internal
 */
final class ObjectDenormalizerTest extends TestCase
{
    /**
     * @return list<array{array<mixed>}>
     */
    public static function provideSuccessfulDenormalizationCases(): iterable
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
     * @param array<mixed> $payload
     *
     * @dataProvider provideSuccessfulDenormalizationCases
     */
    public function testSuccessfulDenormalization(array $payload): void
    {
        $objectDenormalizer = new ObjectDenormalizer();
        $stringDenormalizer = new StringDenormalizer();

        $pointer = Pointer::empty();

        $processedData = $objectDenormalizer->denormalize($payload, $pointer, [
            'foo' => new ObjectField(
                static fn(mixed $fieldData, Pointer $fieldPointer) => $stringDenormalizer->denormalize(
                    $fieldData,
                    $fieldPointer
                ),
                isMandatory: true
            ),
            'bar' => new ObjectField(
                static fn(mixed $fieldData, Pointer $fieldPointer) => $stringDenormalizer->denormalize(
                    $fieldData,
                    $fieldPointer
                ),
                isMandatory: true,
                isNullable: true
            ),
            'baz' => new ObjectField(
                static fn(mixed $fieldData, Pointer $fieldPointer) => $stringDenormalizer->denormalize(
                    $fieldData,
                    $fieldPointer
                ),
                isMandatory: false
            ),
        ]);

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
            $objectDenormalizer->denormalize($data, $pointer, [
                'foo' => new ObjectField(
                    static fn(mixed $fieldData, Pointer $fieldPointer) => $stringDenormalizer->denormalize(
                        $fieldData,
                        $fieldPointer
                    ),
                    isMandatory: true
                ),
                'bar' => new ObjectField(
                    static fn(mixed $fieldData, Pointer $fieldPointer) => $stringDenormalizer->denormalize(
                        $fieldData,
                        $fieldPointer
                    ),
                    isMandatory: true,
                    isNullable: true
                ),
                'baz' => new ObjectField(
                    static fn(mixed $fieldData, Pointer $fieldPointer) => $stringDenormalizer->denormalize(
                        $fieldData,
                        $fieldPointer
                    ),
                    isMandatory: false
                ),
            ]);
        } catch (ValidationError $exception) {
            Assert::assertCount(2, $exception->getViolations());
            Assert::assertContainsOnly(MandatoryFieldMissing::class, $exception->getViolations());
        }
    }
}
