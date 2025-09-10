<?php

declare(strict_types=1);

namespace UserInputProcessor\Exception;

use InvalidArgumentException;
use UserInputProcessor\ConstraintViolation\ConstraintViolationInterface;

final class ValidationError extends InvalidArgumentException
{
    /** @psalm-var non-empty-list<ConstraintViolationInterface> */
    private array $violations;

    /**
     * @psalm-param non-empty-list<ConstraintViolationInterface> $violations
     */
    public function __construct(array $violations)
    {
        $this->violations = $violations;

        parent::__construct(
            message: array_reduce(
                $violations,
                static fn (string $message, ConstraintViolationInterface $violation): string => $message .
                        \sprintf(
                            '%s (%s): %s' . "\n",
                            $violation::getType(),
                            $violation->getPointer()->toString(),
                            $violation->getDescription(),
                        ),
                'Validation errors:' . "\n",
            ),
        );
    }

    /**
     * @psalm-return non-empty-list<ConstraintViolationInterface>
     */
    public function getViolations(): array
    {
        return $this->violations;
    }
}
