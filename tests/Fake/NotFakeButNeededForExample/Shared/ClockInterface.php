<?php

declare(strict_types=1);

namespace Rocky\Faker\Tests\Fake\NotFakeButNeededForExample\Shared;

use DateTimeImmutable;
use DateTimeZone;

interface ClockInterface extends \Psr\Clock\ClockInterface
{
    public function now(?DateTimeZone $timeZone = null): DateTimeImmutable;

    public function microTime(?DateTimeZone $timeZone = null): float;

    /** Use this instead of the native sleep() so that things can be tested without a wait timer. */
    public function sleep(int $seconds): void;
}