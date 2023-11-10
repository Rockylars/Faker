<?php

declare(strict_types=1);

namespace Rocky\Faker\Tests\Fake\Shared;

use DateTimeImmutable;
use DateTimeZone;
use Psr\Log\LoggerInterface;
use Rocky\Faker\Tests\Fake\NotFakeButNeededForExample\Shared\ClockInterface;

final class FakeClock implements ClockInterface
{
    private DateTimeImmutable $now;
    private float $currentMicroTime;

    public function __construct(
        private LoggerInterface $logger,
        DateTimeImmutable $now
    )
    {
        $this->processTime($now);
    }

    public function now(DateTimeZone $timeZone = null): DateTimeImmutable
    {
        return $this->now;
    }

    public function microTime(DateTimeZone $timeZone = null): float
    {
        return $this->currentMicroTime;
    }

    public function sleep(int $seconds): void
    {
        // No sleep, repeat.
        $this->logger->info("Skipped sleeping for $seconds seconds");
    }

    /** Useful for methods that can show a time spent or might work with a time difference */
    public function updateTime(DateTimeImmutable $now): void
    {
        $this->processTime($now);
    }

    private function processTime(DateTimeImmutable $now): void
    {
        $this->now = $now;
        $this->currentMicroTime = (float) $this->now->format('U.u');
    }
}
