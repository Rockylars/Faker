<?php

namespace Rocky\Faker\Tests\Fake\User;

use Rocky\Faker\Faker;
use Rocky\Faker\Tests\Example\User\User;
use Rocky\Faker\Tests\Example\User\UserRepositoryInterface;

final class FakeUserRepository extends Faker implements UserRepositoryInterface
{
    public const FUNCTION_GET_USER_BY_ID = 'getUserById';
    public const FUNCTION_GET_USERS = 'getUsers';
    public const FUNCTION_IS_ACTIVE = 'isActive';
    public const FUNCTION_UPDATE_LAST_LOGIN = 'updateLastLogin';
    public const FUNCTION_DELETE_USER = 'deleteUser';

    public function getUserById(int $userId): User
    {
        return $this->returnOrThrow(__FUNCTION__, [
            'userId' => $userId,
        ]);
    }

    /** @return array<int, User> */
    public function getUsers(): array
    {
        return $this->returnOrThrow(__FUNCTION__, [
            'a call was made',
        ]);
    }

    public function isActive(int $userId): bool
    {
        return $this->returnOrThrow(__FUNCTION__, [
            'userId' => $userId,
        ]);
    }

    public function updateLastLogin(int $userId): void
    {
        $this->voidOrThrow(__FUNCTION__, [
            'userId' => $userId,
        ]);
    }

    public function deleteUser(int $userId): void
    {
        $this->voidOrThrow(__FUNCTION__, [
            'userId' => $userId,
        ]);
    }
}