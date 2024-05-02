<?php

declare(strict_types=1);

namespace Rocky\Faker\Tests\Example\User;

interface UserServiceInterface
{
    public function methodThatRuns(User $user): void;

    public function methodThatUpdates(User $user): void;

    public function methodThatChecks(User $user): void;
}