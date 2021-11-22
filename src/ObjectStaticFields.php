<?php

declare(strict_types=1);

namespace Spiks\UserInputProcessor;

/**
 * This class represents structure and denormalization rules for each field of an object (associative array).
 */
class ObjectStaticFields
{
    /**
     * @param array<string, ObjectField> $fields It is array where key is a key in an object (associative array),
     *                                           and value is {@see ObjectField}
     */
    public function __construct(
        private array $fields,
    ) {
    }

    /**
     * Returns array where key is a key in an object (associative array), and value is {@see ObjectField}.
     *
     * @return array<string, ObjectField>
     */
    public function getFields(): array
    {
        return $this->fields;
    }
}
