<?php

namespace Rocky\Faker\Tests\Fake\User;

use Rocky\Faker\Faker;
use Rocky\Faker\Tests\Example\User\User;
use Rocky\Faker\Tests\Example\User\UserCheckerInterface;
use Rocky\Faker\Tests\Example\User\UserCallServiceInterface;

// This is an example of a class that does multiple things, but those things need to update a record to do things with,
// basically having an intermediate step that is not easily faked through just sending an object as that object is then
// not yet updated and is then also a different reference if it was.
final class FakeUserCallService extends Faker implements UserCallServiceInterface
{
    public const FUNCTION_METHOD_THAT_RUNS = 'methodThatRuns';
    public const FUNCTION_METHOD_THAT_UPDATES = 'methodThatUpdates';
    public const FUNCTION_METHOD_THAT_UPDATES_AND_RETURNS = 'methodThatUpdatesAndReturns';
    public const FUNCTION_METHOD_THAT_CHECKS = 'methodThatChecks';

    public function methodThatRuns(User $user): void
    {
        // You should not save the properties in your project's tests. This is merely to confirm that this here is working.
        $this->fakeCall(__FUNCTION__, [
            'user' => $user,
            'userProperties' => \Safe\json_encode($user)
        ]);
    }

    public function methodThatUpdates(User $user): void
    {
        // You should not save the properties in your project's tests. This is merely to confirm that this here is working.
        $this->fakeCall(__FUNCTION__, [
            'user' => $user,
            'userProperties' => \Safe\json_encode($user)
        ], func_get_args());
    }

    public function methodThatUpdatesAndReturns(User $user): User
    {
        // You should not save the properties in your project's tests. This is merely to confirm that this here is working.
        return $this->fakeCall(__FUNCTION__, [
            'user' => $user,
            'userProperties' => \Safe\json_encode($user)
        ], func_get_args());
    }

    public function methodThatChecks(User $user): void
    {
        // You should not save the properties in your project's tests. This is merely to confirm that this here is working.
        $this->fakeCall(__FUNCTION__, [
            'user' => $user,
            'userProperties' => \Safe\json_encode($user)
        ]);
    }
}