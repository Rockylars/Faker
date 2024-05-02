<?php

namespace Rocky\Faker\Tests\Fake\User;

use Rocky\Faker\Faker;
use Rocky\Faker\Tests\Example\User\User;
use Rocky\Faker\Tests\Example\User\UserCheckerInterface;
use Rocky\Faker\Tests\Example\User\UserServiceInterface;

// This is an example of a class that does multiple things, but those things need to update a record to do things with,
// basically having an intermediate step that is not easily faked through just sending an object as that object is then
// not yet updated and is then also a different reference if it was.
final class FakeUserService extends Faker implements UserServiceInterface
{
    public function methodThatRuns(User $user): void
    {
        // TODO: Implement methodThatRuns() method.
    }

    public function methodThatUpdates(User $user): void
    {
        // TODO: Implement methodThatUpdates() method.
    }

    public function methodThatChecks(User $user): void
    {
        // TODO: Implement methodThatChecks() method.
    }
}