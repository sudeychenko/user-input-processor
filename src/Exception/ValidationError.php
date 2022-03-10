<?php

declare(strict_types=1);

namespace Spiks\UserInputProcessor\Exception;

use InvalidArgumentException;
use Spiks\UserInputProcessor\ConstraintViolation\ConstraintViolationInterface;

final class ValidationError extends InvalidArgumentException
{
    /** @var non-empty-list<ConstraintViolationInterface> */
    private array $violations;

    /**
     * @param non-empty-list<ConstraintViolationInterface> $violations
     */
    public function __construct(
        array $violations
    ) {
        $this->violations = $violations;

        parent::__construct(
            message: array_reduce($violations, static function (string $message, ConstraintViolationInterface $violation): string {
                return $message . sprintf(
                    '%s (%s): %s' . "\n",
                    $violation::getType(),
                    $violation->getPointer()->toString(),
                    $violation->getDescription()
                );
            }, 'Validation errors:' . "\n")
        );
    }

    /**
     * @return non-empty-list<ConstraintViolationInterface>
     */
    public function getViolations(): array
    {
        return $this->violations;
    }
}
