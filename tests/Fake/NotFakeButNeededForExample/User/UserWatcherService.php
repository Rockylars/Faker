<?php

declare(strict_types=1);

namespace Rocky\Faker\Tests\Fake\NotFakeButNeededForExample\User;

final class UserWatcherService
{
    public function __construct(
        public UserCheckerInterface $userChecker,
        public UserRepositoryInterface $userRepository
    ) {}

    public function userObserved(int|User $userOrId): ?int
    {
        $this->userRepository->updateLastLogin(is_int($userOrId) ? $userOrId : $userOrId->id);
        // Yes I know this is a dumb class, I just needed something with a null return and couldn't think of anything
        // smart that would fit the simple example.
        return $this->userChecker->testMethodThatMightReturnNull();
    }
}