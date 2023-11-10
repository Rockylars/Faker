<?php

declare(strict_types=1);

namespace Rocky\Faker\Tests\Unit\TestDeleteUserService;

use Psr\Log\LogLevel;
use Rocky\Faker\Faker;
use Rocky\Faker\Tests\Example\User\DeleteUserService;
use Rocky\Faker\Tests\Example\User\User;
use Rocky\Faker\Tests\Fake\Shared\FakeLogger;
use Rocky\Faker\Tests\Fake\User\FakeUserRepository;
use Rocky\Faker\Tests\Support\UnitTester;

final class DeleteUserServiceCest
{
    private DeleteUserService $deleteUserService;
    private FakeLogger $fakeLogger;
    private FakeUserRepository $fakeUserRepository;

    public function _before(UnitTester $tester): void
    {
        $this->deleteUserService = new DeleteUserService(
            $this->fakeLogger = new FakeLogger(),
            $this->fakeUserRepository = new FakeUserRepository()
        );
    }

    public function deleteUserWillNotDeleteAdmins(UnitTester $tester): void
    {
        $this->fakeUserRepository->setResponsesFor(FakeUserRepository::FUNCTION_DELETE_USER, [
            [Faker::ACTION_VOID => null],
        ]);

        $tester->expectThrowable(
            new \Exception('You can not delete an admin (1|Rocky)'),
            function (): void {
                $this->deleteUserService->deleteUser(self::getExampleAdminUser());
            }
        );

        $tester->assertSame(
            [
            ],
            $this->fakeLogger->getLogs(),
        );
        $tester->assertSame(
            [
            ],
            $this->fakeUserRepository->getAllCallsInStyleSorted()
        );
    }

    public function deleteUserWillNotDeleteAdminsAndWillFetchTheUserIfOnlyAnIdIsSent(UnitTester $tester): void
    {
        $this->fakeUserRepository->setResponsesFor(FakeUserRepository::FUNCTION_GET_USER_BY_ID, [
            [Faker::ACTION_RETURN => self::getExampleAdminUser()],
        ]);
        $this->fakeUserRepository->setResponsesFor(FakeUserRepository::FUNCTION_DELETE_USER, [
            [Faker::ACTION_VOID => null],
        ]);

        $tester->expectThrowable(
            new \Exception('You can not delete an admin (1|Rocky)'),
            function (): void {
                $this->deleteUserService->deleteUser(1);
            }
        );

        $tester->assertSame(
            [
            ],
            $this->fakeLogger->getLogs(),
        );
        $tester->assertSame(
            [
                FakeUserRepository::FUNCTION_GET_USER_BY_ID => [
                    [
                        'userId' => 1,
                    ],
                ],
            ],
            $this->fakeUserRepository->getAllCallsInStyleSorted()
        );
    }

    public function deleteUserWillCheckAndDeleteUserByIdIfNothingIsLinked(UnitTester $tester): void
    {
        $this->fakeUserRepository->setResponsesFor(FakeUserRepository::FUNCTION_DELETE_USER, [
            [Faker::ACTION_VOID => null],
        ]);

        $this->deleteUserService->deleteUser(self::getExampleUser());

        $tester->assertSame(
            [
                [
                    'level' => LogLevel::INFO,
                    'message' => 'Deleted user 2 (NotRocky)',
                    'context' => [],
                ],
            ],
            $this->fakeLogger->getLogs(),
        );
        $tester->assertSame(
            [
                FakeUserRepository::FUNCTION_DELETE_USER => [
                    [
                        'userId' => 2,
                    ],
                ],
            ],
            $this->fakeUserRepository->getAllCallsInStyleSorted()
        );
    }

    public function deleteUserWillCheckAndDeleteUserByIdIfNothingIsLinkedAndWillFetchTheUserIfOnlyAnIdIsSent(UnitTester $tester): void
    {
        $this->fakeUserRepository->setResponsesFor(FakeUserRepository::FUNCTION_GET_USER_BY_ID, [
            [Faker::ACTION_RETURN => self::getExampleUser()],
        ]);
        $this->fakeUserRepository->setResponsesFor(FakeUserRepository::FUNCTION_DELETE_USER, [
            [Faker::ACTION_VOID => null],
        ]);

        $this->deleteUserService->deleteUser(2);

        $tester->assertSame(
            [
                [
                    'level' => LogLevel::INFO,
                    'message' => 'Deleted user 2 (NotRocky)',
                    'context' => [],
                ],
            ],
            $this->fakeLogger->getLogs(),
        );
        $tester->assertSame(
            [
                FakeUserRepository::FUNCTION_DELETE_USER => [
                    [
                        'userId' => 2,
                    ],
                ],
                FakeUserRepository::FUNCTION_GET_USER_BY_ID => [
                    [
                        'userId' => 2,
                    ],
                ],
            ],
            $this->fakeUserRepository->getAllCallsInStyleSorted()
        );
    }

    private static function getExampleAdminUser(): User
    {
        return new User(
            1,
            'Rocky',
            true,
            \DateTimeImmutable::createFromFormat(
                '!Y-m-d H:i:s',
                '2023-02-17 12:13:14',
                new \DateTimeZone('Europe/Amsterdam')
            ),
        );
    }

    private static function getExampleUser(): User
    {
        return new User(
            2,
            'NotRocky',
            false,
            \DateTimeImmutable::createFromFormat(
                '!Y-m-d H:i:s',
                '2023-02-13 12:11:14',
                new \DateTimeZone('Europe/Amsterdam')
            ),
        );
    }
}