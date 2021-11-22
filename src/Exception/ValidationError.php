<?php

declare(strict_types=1);

namespace Spiks\UserInputProcessor\Exception;

use InvalidArgumentException;
use Spiks\UserInputProcessor\ConstraintViolation\ConstraintViolationCollection;

final class ValidationError extends InvalidArgumentException
{
    public function __construct(
        private ConstraintViolationCollection $violations
    ) {
        parent::__construct(
            message: $violations->__toString()
        );
    }

    public function getViolations(): ConstraintViolationCollection
    {
        return $this->violations;
    }
}
