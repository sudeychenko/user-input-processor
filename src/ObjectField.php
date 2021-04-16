<?php

declare(strict_types=1);

namespace Flaksp\UserInputProcessor;

use Closure;

class ObjectField
{
    public function __construct(
        private Closure $denormalizer,
        private bool $isMandatory = true
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
}
