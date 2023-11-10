<?php

declare(strict_types=1);

namespace Rocky\Faker\Tests\Example\User;

interface UserCheckerInterface
{
    public function testMethodThatMightReturnNull(): ?int;
}