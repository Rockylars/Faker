<?php

namespace Rocky\Faker\Tests\Fake\NotFakeButNeededForExample\User;

final class User
{
    public function __construct(
        public int $id,
        public string $name,
        public bool $isAdmin,
        public \DateTimeImmutable $lastLogin
    ) {}
}