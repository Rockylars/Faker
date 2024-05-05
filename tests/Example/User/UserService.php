<?php

declare(strict_types=1);

namespace Rocky\Faker\Tests\Example\User;

use Psr\Log\LoggerInterface;
use Exception;

final class UserService
{
    public function __construct(
        private UserCallServiceInterface $userCallService
    ) {}

    public function updateUser(User $user): void
    {
        $this->userCallService->methodThatRuns($user);
        $this->userCallService->methodThatUpdates($user);
        $this->userCallService->methodThatChecks($this->userCallService->methodThatUpdatesAndReturns($user));
    }
}