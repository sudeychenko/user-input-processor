<?php

declare(strict_types=1);

namespace Tests\UserInputProcessor\Denormalizer;

use PHPUnit\Framework\TestCase;
use Tests\UserInputProcessor\Stab\Enum\TestCaseEnum;
use UserInputProcessor\ConstraintViolation\EnumValueIsNotAllowed;
use UserInputProcessor\Denormalizer\EnumerationDenormalizer;
use UserInputProcessor\Denormalizer\StringDenormalizer;
use UserInputProcessor\Exception\ValidationError;
use UserInputProcessor\Pointer;

/**
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
            enumClassName: TestCaseEnum::class,
        );
        $this->assertSame('case_1', $processedData->value);

        $processedData = $enumerationDenormalizer->denormalize(
            data: 'case_2',
            pointer: $pointer,
            enumClassName: TestCaseEnum::class,
            allowedValues: [TestCaseEnum::CASE_2],
        );
        $this->assertSame('case_2', $processedData->value);
    }

    public function testUnsuccessfulDenormalization(): void
    {
        $enumerationDenormalizer = new EnumerationDenormalizer(new StringDenormalizer());
        $pointer = Pointer::empty();

        try {
            $enumerationDenormalizer->denormalize(
                data: 'case_3',
                pointer: $pointer,
                enumClassName: TestCaseEnum::class,
            );
        } catch (ValidationError $exception) {
            $this->assertCount(1, $exception->getViolations());
            $validationErrors = $exception->getViolations();
            $this->assertInstanceOf(EnumValueIsNotAllowed::class, array_pop($validationErrors));
        }

        try {
            $enumerationDenormalizer->denormalize(
                data: 'case_1',
                pointer: $pointer,
                enumClassName: TestCaseEnum::class,
                allowedValues: [TestCaseEnum::CASE_2],
            );
        } catch (ValidationError $exception) {
            $this->assertCount(1, $exception->getViolations());
            $validationErrors = $exception->getViolations();
            $this->assertInstanceOf(EnumValueIsNotAllowed::class, array_pop($validationErrors));
        }
    }
}
