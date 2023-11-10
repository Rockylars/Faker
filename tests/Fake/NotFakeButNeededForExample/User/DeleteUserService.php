<?php

declare(strict_types=1);

namespace Rocky\Faker\Tests\Fake\NotFakeButNeededForExample\User;

use Psr\Log\LoggerInterface;

final class DeleteUserService
{
    public function __construct(
        public LoggerInterface $logger,
        public UserRepositoryInterface $userRepository
    ) {}

    public function deleteUser(int|User $userOrId): void
    {
        $user = is_int($userOrId)
            ? $this->userRepository->getUserById($userOrId)
            : $userOrId;

        if ($user->isAdmin) {
            throw new \Exception("You can not delete an admin ($user->id|$user->name)");
        }

        $this->userRepository->deleteUser($user->id);
        $this->logger->info("Deleted user $user->id ($user->name)");
    }
}