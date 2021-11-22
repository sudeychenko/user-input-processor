<?php

declare(strict_types=1);

namespace Tests\Spiks\UserInputProcessor\ConstraintViolation;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Spiks\UserInputProcessor\ConstraintViolation\ConstraintViolationCollection;
use Spiks\UserInputProcessor\ConstraintViolation\MandatoryFieldMissing;
use Spiks\UserInputProcessor\Pointer;

/**
 * @covers \Spiks\UserInputProcessor\ConstraintViolation\ConstraintViolationCollection
 *
 * @internal
 */
final class ConstraintViolationCollectionTest extends TestCase
{
    public function testSuccessfulToStringTransformation(): void
    {
        $violations = new ConstraintViolationCollection([
            new MandatoryFieldMissing(Pointer::empty()),
            new MandatoryFieldMissing(Pointer::empty()),
            new MandatoryFieldMissing(Pointer::empty()),
        ]);

        $expectedMessage = <<<'MESSAGE'
            1) mandatory_field_missing (): Property is mandatory, but it's missing. Even if field is nullable it should be presented in request payload.
            2) mandatory_field_missing (): Property is mandatory, but it's missing. Even if field is nullable it should be presented in request payload.
            3) mandatory_field_missing (): Property is mandatory, but it's missing. Even if field is nullable it should be presented in request payload.

            MESSAGE;

        Assert::assertEquals($expectedMessage, $violations->__toString());
    }
}
