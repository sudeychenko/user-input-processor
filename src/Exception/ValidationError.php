<?php

declare(strict_types=1);

namespace Flaksp\UserInputProcessor\Exception;

use Flaksp\UserInputProcessor\ConstraintViolation\ConstraintViolationCollection;
use InvalidArgumentException;

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
