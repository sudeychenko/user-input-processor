<?php

declare(strict_types=1);

namespace Flaksp\UserInputProcessor;

use InvalidArgumentException;

/**
 * Denormalization rules that may differ depending on the discriminator value.
 */
class ObjectDiscriminatorFields
{
    /**
     * @param array<string, ObjectStaticFields> $fields Array key is a discriminator value, array value contains
     *                                                  denormalization rules for each field in associative array
     */
    public function __construct(
        private array $fields,
    ) {
    }

    /**
     * All possible values of the discriminator.
     *
     * @return string[]
     */
    public function getPossibleDiscriminatorValues(): array
    {
        return array_keys($this->fields);
    }

    /**
     * Returns associative array denormalization rules depending on discirminator value.
     *
     * @param string $discriminatorValue Discriminator value
     *
     * @return ObjectStaticFields Denormalization rules
     */
    public function getStaticFieldsByDiscriminatorValue(string $discriminatorValue): ObjectStaticFields
    {
        if (!\array_key_exists($discriminatorValue, $this->fields)) {
            throw new InvalidArgumentException('Unknown value: ' . $discriminatorValue);
        }

        return $this->fields[$discriminatorValue];
    }
}
