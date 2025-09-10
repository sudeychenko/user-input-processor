<?php

declare(strict_types=1);

namespace UserInputProcessor;

use DateTimeImmutable;

final readonly class DateTimeRange
{
    public function __construct(public DateTimeImmutable $from, public DateTimeImmutable $to)
    {
    }
}
