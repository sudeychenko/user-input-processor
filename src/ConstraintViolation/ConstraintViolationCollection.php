<?php

declare(strict_types=1);

namespace Flaksp\UserInputDeserializer\ConstraintViolation;

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;
use OutOfBoundsException;
use Stringable;

class ConstraintViolationCollection implements IteratorAggregate, Countable, ArrayAccess, Stringable
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

    /**
     * {@inheritdoc}
     */
    public function add(ConstraintViolationInterface $violation): void
    {
        $this->violations[] = $violation;
    }

    /**
     * {@inheritdoc}
     */
    public function addAll(self $otherList): void
    {
        foreach ($otherList as $violation) {
            $this->violations[] = $violation;
        }
    }

    /**
     * @return int
     */
    public function count()
    {
        return \count($this->violations);
    }

    /**
     * {@inheritdoc}
     */
    public function get(int $offset)
    {
        if (!isset($this->violations[$offset])) {
            throw new OutOfBoundsException(sprintf('The offset "%s" does not exist.', $offset));
        }

        return $this->violations[$offset];
    }

    /**
     * {@inheritdoc}
     *
     * @return ArrayIterator|ConstraintViolationInterface[]
     */
    public function getIterator(): ArrayIterator | array
    {
        return new ArrayIterator($this->violations);
    }

    /**
     * {@inheritdoc}
     */
    public function has(int $offset)
    {
        return isset($this->violations[$offset]);
    }

    /**
     * @param mixed $offset
     *
     * @return bool
     */
    public function offsetExists($offset)
    {
        return $this->has($offset);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($offset, $violation): void
    {
        if (null === $offset) {
            $this->add($violation);
        } else {
            $this->set($offset, $violation);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($offset): void
    {
        $this->remove($offset);
    }

    /**
     * {@inheritdoc}
     */
    public function remove(int $offset): void
    {
        unset($this->violations[$offset]);
    }

    /**
     * {@inheritdoc}
     */
    public function set(int $offset, ConstraintViolationInterface $violation): void
    {
        $this->violations[$offset] = $violation;
    }

    /**
     * Converts the violation into a string for debugging purposes.
     *
     * @return string The violation as string
     */
    public function __toString(): string
    {
        $violationIndex = 1;

        return array_reduce($this->violations, static function (string $carry, ConstraintViolationInterface $violation) use (&$violationIndex): string {
            return $carry .= sprintf(
                '%d) %s (%s): %s' . "\n",
                $violationIndex++,
                $violation->getPointer()->getPointer(),
                $violation->getType(),
                $violation->getDescription()
            );
        }, "\n");
    }
}
