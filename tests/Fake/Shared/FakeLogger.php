<?php

declare(strict_types=1);

namespace Rocky\Faker\Tests\Fake\Shared;

use Psr\Log\AbstractLogger;
use Stringable;

final class FakeLogger extends AbstractLogger
{
    /** @var list<array<string, mixed>> */
    private array $logs = [];

    /** @param mixed[] $context */
    public function log(mixed $level, Stringable|string $message, array $context = []): void
    {
        $this->logs[] = [
            'level' => $level,
            'message' => $message,
            'context' => $context,
        ];
    }

    /** @return list<array<string, mixed>> */
    public function getLogs(): array
    {
        return $this->logs;
    }
}
