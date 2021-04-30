<?php

declare(strict_types=1);

namespace Flaksp\UserInputProcessor;

use InvalidArgumentException;

class ObjectDiscriminatorFields
{
    /**
     * @param array<string, ObjectStaticFields> $fields
     */
    public function __construct(
        private array $fields,
    ) {
    }

    /**
     * @return string[]
     */
    public function getPossibleDiscriminatorValues(): array
    {
        return array_keys($this->fields);
    }

    public function getStaticFieldsByDiscriminatorValue(string $discriminatorValue): ObjectStaticFields
    {
        if (!\array_key_exists($discriminatorValue, $this->fields)) {
            throw new InvalidArgumentException('Unknown value: ' . $discriminatorValue);
        }

        return $this->fields[$discriminatorValue];
    }
}
