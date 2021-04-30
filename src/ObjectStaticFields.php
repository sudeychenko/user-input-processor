<?php

declare(strict_types=1);

namespace Flaksp\UserInputProcessor;

class ObjectStaticFields
{
    /**
     * @param array<string, ObjectField> $fields
     */
    public function __construct(
        private array $fields,
    ) {
    }

    /**
     * @return array<string, ObjectField>
     */
    public function getFields(): array
    {
        return $this->fields;
    }
}
