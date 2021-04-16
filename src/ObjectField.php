<?php

declare(strict_types=1);

namespace Flaksp\UserInputProcessor;

use Closure;

class ObjectField
{
    public function __construct(
        private Closure $deserializer,
        private bool $isMandatory = true
    ) {
    }

    public function getDeserializer(): Closure
    {
        return $this->deserializer;
    }

    public function isMandatory(): bool
    {
        return $this->isMandatory;
    }
}
