<?php

declare(strict_types=1);

namespace Flaksp\UserInputDeserializer;

class ObjectStaticFields
{
    /**
     * @param $fields array<string, ObjectField>
     */
    public function __construct(
        private array $fields,
    ) {
    }

    /**
     * @param $properties array<string, ObjectField>
     */
    public function getFields(): array
    {
        return $this->fields;
    }
}
