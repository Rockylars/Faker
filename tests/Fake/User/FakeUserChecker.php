<?php

namespace Rocky\Faker\Tests\Fake\User;

use Rocky\Faker\Faker;
use Rocky\Faker\Tests\Example\User\UserCheckerInterface;

final class FakeUserChecker extends Faker implements UserCheckerInterface
{
    public const FUNCTION_TEST_METHOD_THAT_MIGHT_RETURN_NULL = 'testMethodThatMightReturnNull';

    public function testMethodThatMightReturnNull(): ?int
    {
        return $this->returnOrThrow(__FUNCTION__, [
            'a call was made'
        ]);
    }
}