<?php

declare(strict_types=1);

namespace Flaksp\UserInputDeserializer\Exception;

use Flaksp\UserInputDeserializer\ConstraintViolation\ConstraintViolationCollection;
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
