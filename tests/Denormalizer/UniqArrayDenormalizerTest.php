<?php

declare(strict_types=1);

namespace Tests\Spiks\UserInputProcessor\Denormalizer;

use Closure;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Spiks\UserInputProcessor\ConstraintViolation\ArrayIsNotUnique;
use Spiks\UserInputProcessor\Denormalizer\ArrayDenormalizer;
use Spiks\UserInputProcessor\Denormalizer\IntegerDenormalizer;
use Spiks\UserInputProcessor\Denormalizer\StringDenormalizer;
use Spiks\UserInputProcessor\Denormalizer\UniqueArrayDenormalizer;
use Spiks\UserInputProcessor\Exception\ValidationError;
use Spiks\UserInputProcessor\Pointer;

/**
 * @covers \Spiks\UserInputProcessor\Denormalizer\UniqueArrayDenormalizer
 *
 * @internal
 */
final class UniqArrayDenormalizerTest extends TestCase
{
    /**
     * @psalm-return list<array{
     *     0: list<int|string>,
     *     1: Closure(int|string, Pointer): (int|string),
     * }>
     */
    public static function provideSuccessfulDenormalizationCases(): iterable
    {
        return [
            [
                [1, 2, 3, 4, 5, 6, 7, 8],
                static fn (mixed $value, Pointer $pointer): int => (new IntegerDenormalizer())->denormalize(
                    data: $value,
                    pointer: $pointer,
                ),
            ],
            [
                ['a', 'b', 'c', 'd', 'e', 'f'],
                static fn (mixed $value, Pointer $pointer): string => (new StringDenormalizer())->denormalize(
                    data: $value,
                    pointer: $pointer,
                ),
            ],
            [
                ['a'],
                static fn (mixed $value, Pointer $pointer): string => (new StringDenormalizer())->denormalize(
                    data: $value,
                    pointer: $pointer,
                ),
            ],
            [
                [],
                static fn (mixed $value, Pointer $pointer): string => (new StringDenormalizer())->denormalize(
                    data: $value,
                    pointer: $pointer,
                ),
            ],
        ];
    }

    /**
     * @psalm-param list<int|string> $payload
     * @psalm-param Closure(mixed, Pointer): (int|string) $denormalizerClosure
     *
     * @dataProvider provideSuccessfulDenormalizationCases
     */
    public function testSuccessfulDenormalization(
        array $payload,
        Closure $denormalizerClosure,
    ): void {
        $arrayDenormalizer = new ArrayDenormalizer();
        $uniqArrayDenormalizer = new UniqueArrayDenormalizer($arrayDenormalizer);
        $pointer = Pointer::empty();

        $processedData = $uniqArrayDenormalizer->denormalize(
            data: $payload,
            pointer: $pointer,
            denormalizer: $denormalizerClosure,
            uniqueKeyProvider: static fn (int|string $val): string => (string) $val,
        );

        Assert::assertCount(\count($payload), $processedData);
        Assert::assertCount(\count($payload), array_unique($processedData));
    }

    public function testUnsuccessfulDenormalization(): void
    {
        $integerDenormalizer = new IntegerDenormalizer();
        $arrayDenormalizer = new ArrayDenormalizer();
        $uniqArrayDenormalizer = new UniqueArrayDenormalizer($arrayDenormalizer);
        $pointer = Pointer::empty();

        try {
            $uniqArrayDenormalizer->denormalize(
                data: [1, 2, 3, 4, 5, 6, 7, 8, 9, 1],
                pointer: $pointer,
                denormalizer: static fn (mixed $val, Pointer $pointer): int => $integerDenormalizer->denormalize(
                    data: $val,
                    pointer: $pointer,
                ),
                uniqueKeyProvider: static fn (string|int $val): string => (string) $val,
                minItems: 1,
                maxItems: 10
            );
        } catch (ValidationError $exception) {
            Assert::assertCount(1, $exception->getViolations());
            Assert::assertContainsOnly(ArrayIsNotUnique::class, $exception->getViolations());
        }
    }
}
