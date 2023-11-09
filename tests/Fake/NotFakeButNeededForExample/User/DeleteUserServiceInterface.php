<?php

declare(strict_types=1);

namespace Rocky\Faker\Tests\Fake\NotFakeButNeededForExample\User;

interface DeleteUserServiceInterface
{
    public function deleteUser(int|User $userOrId): void;
}