<?php

declare(strict_types=1);

namespace Spiks\UserInputProcessor;

use DateTimeImmutable;

class DateTimeRange
{
    public function __construct(public readonly DateTimeImmutable $from, public readonly DateTimeImmutable $to)
    {
    }
}
