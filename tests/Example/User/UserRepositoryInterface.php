<?php

declare(strict_types=1);

namespace Rocky\Faker\Tests\Example\User;

interface UserRepositoryInterface
{
    public function getUserById(int $userId): User;

    /** @return array<int, User> */
    public function getUsers(): array;

    public function isActive(int $userId): bool;

    public function updateLastLogin(int $userId): void;

    public function deleteUser(int $userId): void;
}