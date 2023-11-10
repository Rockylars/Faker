<?php

declare(strict_types=1);

namespace Rocky\Faker\Tests\Fake\NotFakeButNeededForExample\User;

interface UserCheckerInterface
{
    public function testMethodThatMightReturnNull(): ?int;
}