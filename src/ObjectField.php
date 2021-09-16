<?php

declare(strict_types=1);

namespace Flaksp\UserInputProcessor;

use Closure;

class ObjectField
{
    public function __construct(
        private Closure $denormalizer,
        private bool $isMandatory = true,
        private bool $isNullable = false,
    ) {
    }

    public function getDenormalizer(): Closure
    {
        return $this->denormalizer;
    }

    public function isMandatory(): bool
    {
        return $this->isMandatory;
    }

    public function isNullable(): bool
    {
        return $this->isNullable;
    }
}
