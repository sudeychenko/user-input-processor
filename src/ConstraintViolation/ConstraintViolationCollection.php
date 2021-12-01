<?php

declare(strict_types=1);

namespace Spiks\UserInputProcessor\ConstraintViolation;

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;
use OutOfBoundsException;

/**
 * @implements IteratorAggregate<int, ConstraintViolationInterface>
 */
class ConstraintViolationCollection implements IteratorAggregate, Countable, ArrayAccess
{
    /** @var ConstraintViolationInterface[] */
    private array $violations = [];

    /**
     * Creates a new constraint violation list.
     *
     * @param ConstraintViolationInterface[] $violations The constraint violations to add to the list
     */
    public function __construct(array $violations = [])
    {
        foreach ($violations as $violation) {
            $this->add($violation);
        }
    }

    public function add(ConstraintViolationInterface $violation): void
    {
        $this->violations[] = $violation;
    }

    public function addAll(self $otherList): void
    {
        /** @var ConstraintViolationInterface $violation */
        foreach ($otherList as $violation) {
            $this->add($violation);
        }
    }

    public function count(): int
    {
        return \count($this->violations);
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->violations);
    }

    public function isNotEmpty(): bool
    {
        return 0 !== $this->count();
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($offset): bool
    {
        return isset($this->violations[$offset]);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($offset): ConstraintViolationInterface
    {
        if (!isset($this->violations[$offset])) {
            throw new OutOfBoundsException(sprintf('The offset "%s" does not exist.', $offset));
        }

        return $this->violations[$offset];
    }

    /**
     * {@inheritdoc}
     *
     * @param mixed                        $offset
     * @param ConstraintViolationInterface $violation
     */
    public function offsetSet($offset, $violation): void
    {
        if (null === $offset) {
            $this->violations[] = $violation;
        } else {
            $this->violations[$offset] = $violation;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($offset): void
    {
        unset($this->violations[$offset]);
    }

    /**
     * Converts the violation into an array for debugging purposes.
     *
     * @return array<array{pointer: string, type: string, description: string}> Collection of violations as array
     *
     * @internal Should not be used outside of the library
     */
    public function toArray(): array
    {
        return array_map(static fn (ConstraintViolationInterface $violation) => [
            'pointer' => $violation->getPointer()->toString(),
            'type' => $violation::getType(),
            'description' => $violation->getDescription(),
        ], $this->violations);
    }

    /**
     * Converts the violation into a string for debugging purposes.
     *
     * @return string Collection of violations as string
     *
     * @internal Should not be used outside of the library
     */
    public function toString(): string
    {
        $violationIndex = 1;

        return array_reduce($this->violations, static function (string $carry, ConstraintViolationInterface $violation) use (&$violationIndex): string {
            return $carry .= sprintf(
                '%d) %s (%s): %s' . "\n",
                $violationIndex++,
                $violation->getType(),
                $violation->getPointer()->toString(),
                $violation->getDescription()
            );
        }, '');
    }
}
