<?php

declare(strict_types=1);

namespace Tests\UserInputProcessor\Denormalizer;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use UserInputProcessor\ConstraintViolation\MandatoryFieldMissing;
use UserInputProcessor\Denormalizer\ObjectDenormalizer;
use UserInputProcessor\Denormalizer\StringDenormalizer;
use UserInputProcessor\Exception\ValidationError;
use UserInputProcessor\ObjectField;
use UserInputProcessor\Pointer;

/**
 * @internal
 */
final class ObjectDenormalizerTest extends TestCase
{
    /**
     * @psalm-param non-empty-array<string, string|null> $payload
     */
    #[DataProvider('provideSuccessfulDenormalizationCases')]
    public function testSuccessfulDenormalization(array $payload): void
    {
        $objectDenormalizer = new ObjectDenormalizer();
        $stringDenormalizer = new StringDenormalizer();

        $pointer = Pointer::empty();

        $processedData = $objectDenormalizer->denormalize($payload, $pointer, [
            'foo' => new ObjectField(
                static fn(mixed $fieldData, Pointer $fieldPointer) => $stringDenormalizer->denormalize(
                    $fieldData,
                    $fieldPointer,
                ),
                isMandatory: true,
            ),
            'bar' => new ObjectField(
                static fn(mixed $fieldData, Pointer $fieldPointer) => $stringDenormalizer->denormalize(
                    $fieldData,
                    $fieldPointer,
                ),
                isMandatory: true,
                isNullable: true,
            ),
            'baz' => new ObjectField(
                static fn(mixed $fieldData, Pointer $fieldPointer) => $stringDenormalizer->denormalize(
                    $fieldData,
                    $fieldPointer,
                ),
                isMandatory: false,
            ),
        ]);

        $this->assertSame($payload, $processedData);
    }

    /**
     * @psalm-return non-empty-list<non-empty-list<non-empty-array<string, string|null>>>
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
                        $fieldPointer,
                    ),
                    isMandatory: true,
                ),
                'bar' => new ObjectField(
                    static fn(mixed $fieldData, Pointer $fieldPointer) => $stringDenormalizer->denormalize(
                        $fieldData,
                        $fieldPointer,
                    ),
                    isMandatory: true,
                    isNullable: true,
                ),
                'baz' => new ObjectField(
                    static fn(mixed $fieldData, Pointer $fieldPointer) => $stringDenormalizer->denormalize(
                        $fieldData,
                        $fieldPointer,
                    ),
                    isMandatory: false,
                ),
            ]);
        } catch (ValidationError $exception) {
            $this->assertCount(2, $exception->getViolations());
            $validationErrors = $exception->getViolations();
            $this->assertInstanceOf(MandatoryFieldMissing::class, array_pop($validationErrors));
            $this->assertInstanceOf(MandatoryFieldMissing::class, array_pop($validationErrors));
        }
    }
}
