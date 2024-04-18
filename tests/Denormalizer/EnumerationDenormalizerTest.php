<?php

declare(strict_types=1);

namespace Tests\Spiks\UserInputProcessor\Denormalizer;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Spiks\UserInputProcessor\ConstraintViolation\EnumValueIsNotAllowed;
use Spiks\UserInputProcessor\Denormalizer\EnumerationDenormalizer;
use Spiks\UserInputProcessor\Denormalizer\StringDenormalizer;
use Spiks\UserInputProcessor\Exception\ValidationError;
use Spiks\UserInputProcessor\Pointer;
use Tests\Spiks\UserInputProcessor\Stab\Enum\TestCaseEnum;

/**
 * @covers \Spiks\UserInputProcessor\Denormalizer\EnumerationDenormalizer
 *
 * @internal
 */
final class EnumerationDenormalizerTest extends TestCase
{
    public function testSuccessfulDenormalization(): void
    {
        $enumerationDenormalizer = new EnumerationDenormalizer(new StringDenormalizer());
        $pointer = Pointer::empty();

        $processedData = $enumerationDenormalizer->denormalize(
            data: 'case_1',
            pointer: $pointer,
            enumClassName: TestCaseEnum::class
        );
        Assert::assertSame('case_1', $processedData->value);

        $processedData = $enumerationDenormalizer->denormalize(
            data: 'case_2',
            pointer: $pointer,
            enumClassName: TestCaseEnum::class,
            allowedValues: [TestCaseEnum::CASE_2]
        );
        Assert::assertSame('case_2', $processedData->value);
    }

    public function testUnsuccessfulDenormalization(): void
    {
        $enumerationDenormalizer = new EnumerationDenormalizer(new StringDenormalizer());
        $pointer = Pointer::empty();

        try {
            $enumerationDenormalizer->denormalize(
                data: 'case_3',
                pointer: $pointer,
                enumClassName: TestCaseEnum::class
            );
        } catch (ValidationError $exception) {
            Assert::assertCount(1, $exception->getViolations());
            Assert::assertContainsOnly(EnumValueIsNotAllowed::class, $exception->getViolations());
        }

        try {
            $enumerationDenormalizer->denormalize(
                data: 'case_1',
                pointer: $pointer,
                enumClassName: TestCaseEnum::class,
                allowedValues: [TestCaseEnum::CASE_2]
            );
        } catch (ValidationError $exception) {
            Assert::assertCount(1, $exception->getViolations());
            Assert::assertContainsOnly(EnumValueIsNotAllowed::class, $exception->getViolations());
        }
    }
}
