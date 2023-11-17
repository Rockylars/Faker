<?php

namespace Rocky\Faker\Tests\Example\User;
use DateTimeImmutable;

final class User
{
    public function __construct(
        public int $id,
        public string $name,
        public bool $isAdmin,
        public DateTimeImmutable $lastLogin
    ) {}
}