<?php

declare(strict_types=1);

namespace Rocky\Faker\Tests\Unit\TestFaker;

use Exception;
use Rocky\Faker\Faker;
use Rocky\Faker\Tests\Example\User\DeleteUserService;
use Rocky\Faker\Tests\Example\User\User;
use Rocky\Faker\Tests\Example\User\UserService;
use Rocky\Faker\Tests\Example\User\UserWatcherService;
use Rocky\Faker\Tests\Fake\Shared\FakeLogger;
use Rocky\Faker\Tests\Fake\User\FakeUserCallService;
use Rocky\Faker\Tests\Fake\User\FakeUserChecker;
use Rocky\Faker\Tests\Fake\User\FakeUserRepository;
use Rocky\Faker\Tests\Support\UnitTester;
use RuntimeException;
use DateTimeZone;

final class FakerCest
{
    private DeleteUserService $deleteUserService;
    private FakeUserRepository $fakeUserRepository;
    private UserWatcherService $userWatcherService;
    private FakeUserChecker $fakeUserChecker;

    public function _before(UnitTester $tester): void
    {
        $this->deleteUserService = new DeleteUserService(
            new FakeLogger(),
            $this->fakeUserRepository = new FakeUserRepository()
        );
        $this->userWatcherService = new UserWatcherService(
            $this->fakeUserChecker = new FakeUserChecker(),
            $this->fakeUserRepository
        );
    }

    public function fakeCallFailsWhenNoResponsesAreSet(UnitTester $tester): void
    {
        $tester->expectThrowable(
            new Exception('No responses defined for Rocky\Faker\Tests\Fake\User\FakeUserRepository::getUserById, add them to the setResponsesFor'),
            function (): void {
                $this->deleteUserService->deleteUser(2);
            }
        );
    }

    public function fakeCallFailsWhenNotEnoughResponsesAreSet(UnitTester $tester): void
    {
        $this->fakeUserRepository->setResponsesFor(FakeUserRepository::FUNCTION_GET_USER_BY_ID, [
            [Faker::ACTION_RETURN => self::getExampleUser()],
        ]);
        $this->fakeUserRepository->setResponsesFor(FakeUserRepository::FUNCTION_DELETE_USER, [
            [Faker::ACTION_VOID => null],
        ]);

        $this->deleteUserService->deleteUser(2);

        $tester->expectThrowable(
            new Exception('Not enough responses defined for Rocky\Faker\Tests\Fake\User\FakeUserRepository::getUserById, add more to the setResponsesFor'),
            function (): void {
                $this->deleteUserService->deleteUser(2);
            }
        );
    }

    public function fakeCallWillReturnAndVoidAndThrowMultipleResponses(UnitTester $tester): void
    {
        $this->fakeUserRepository->setResponsesFor(FakeUserRepository::FUNCTION_GET_USER_BY_ID, [
            [Faker::ACTION_RETURN => self::getExampleUser()],
            [Faker::ACTION_THROW => $exceptionThrown2 = new RuntimeException('Could not find user 2')],
            [Faker::ACTION_RETURN => self::getExampleUser()],
            [Faker::ACTION_RETURN => self::getExampleUser()],
        ]);
        $this->fakeUserRepository->setResponsesFor(FakeUserRepository::FUNCTION_DELETE_USER, [
            [Faker::ACTION_VOID => null],
            // Not called as it failed earlier.
            [Faker::ACTION_THROW => $exceptionThrown3 = new RuntimeException('Database timeout exception')],
            [Faker::ACTION_VOID => null],
        ]);

        $this->deleteUserService->deleteUser(2);

        $tester->expectThrowable(
            $exceptionThrown2,
            function (): void {
                $this->deleteUserService->deleteUser(2);
            }
        );

        $tester->expectThrowable(
            $exceptionThrown3,
            function (): void {
                $this->deleteUserService->deleteUser(2);
            }
        );

        $this->deleteUserService->deleteUser(2);
    }

    public function fakeCallWillReturnNullValues(UnitTester $tester): void
    {
        $this->fakeUserChecker->setResponsesFor(FakeUserChecker::FUNCTION_TEST_METHOD_THAT_MIGHT_RETURN_NULL, [
            [Faker::ACTION_RETURN => null],
        ]);
        $this->fakeUserRepository->setResponsesFor(FakeUserRepository::FUNCTION_UPDATE_LAST_LOGIN, [
            [Faker::ACTION_VOID => null],
        ]);

        // This assert is not needed, but it is nice to see it come all the way out.
        $tester->assertNull($this->userWatcherService->userObserved(7));
    }

    public function fakeCallWillExecuteFunctions(UnitTester $tester): void
    {
        $userService = new UserService(
            $userCallService = new FakeUserCallService()
        );

        $userCallService->setResponsesFor(FakeUserCallService::FUNCTION_METHOD_THAT_RUNS, [
            [Faker::ACTION_VOID => null],
        ]);
        $userCallService->setResponsesFor(FakeUserCallService::FUNCTION_METHOD_THAT_UPDATES, [
            [Faker::ACTION_FUNCTION => static function (User $user): void {
                $user->name = strrev($user->name);
            }],
        ]);
        $userCallService->setResponsesFor(FakeUserCallService::FUNCTION_METHOD_THAT_UPDATES_AND_RETURNS, [
            [Faker::ACTION_FUNCTION => static function (User $user): User {
                $user->name = str_repeat($user->name, 2);
                return $user;
            }],
        ]);
        $userCallService->setResponsesFor(FakeUserCallService::FUNCTION_METHOD_THAT_CHECKS, [
            [Faker::ACTION_VOID => null],
        ]);

        $user = new User(
            1,
            'UserName',
            false,
            \Safe\DateTimeImmutable::createFromFormat(
                '!Y-m-d H:i:s',
                '2023-02-17 12:13:14',
                new DateTimeZone('Europe/Amsterdam')
            )
        );
        $userService->updateUser($user);

        // You should not save the properties in your project's tests. This is merely to confirm that this here is working.
        $tester->assertSame(
            [
                FakeUserCallService::FUNCTION_METHOD_THAT_CHECKS => [
                    [
                        'user' => $user,
                        'userProperties' => '{"id":1,"name":"emaNresUemaNresU","isAdmin":false,"lastLogin":{"date":"2023-02-17 12:13:14.000000","timezone_type":3,"timezone":"Europe\/Amsterdam"}}'
                    ],
                ],
                FakeUserCallService::FUNCTION_METHOD_THAT_RUNS => [
                    [
                        'user' => $user,
                        'userProperties' => '{"id":1,"name":"UserName","isAdmin":false,"lastLogin":{"date":"2023-02-17 12:13:14.000000","timezone_type":3,"timezone":"Europe\/Amsterdam"}}'
                    ],
                ],
                FakeUserCallService::FUNCTION_METHOD_THAT_UPDATES => [
                    [
                        'user' => $user,
                        'userProperties' => '{"id":1,"name":"UserName","isAdmin":false,"lastLogin":{"date":"2023-02-17 12:13:14.000000","timezone_type":3,"timezone":"Europe\/Amsterdam"}}'
                    ],
                ],
                FakeUserCallService::FUNCTION_METHOD_THAT_UPDATES_AND_RETURNS => [
                    [
                        'user' => $user,
                        'userProperties' => '{"id":1,"name":"emaNresU","isAdmin":false,"lastLogin":{"date":"2023-02-17 12:13:14.000000","timezone_type":3,"timezone":"Europe\/Amsterdam"}}'
                    ],
                ],
            ],
            $userCallService->getAllCallsInStyleSorted()
        );
    }

    /** @see FakerCest::fakeCallWillReturnAndVoidAndThrowMultipleResponses */
    public function addResponseForWillAddResponses(UnitTester $tester): void
    {
        $this->fakeUserRepository->addResponseFor(FakeUserRepository::FUNCTION_GET_USER_BY_ID,
            [Faker::ACTION_RETURN => self::getExampleUser()]
        );
        $this->fakeUserRepository->addResponseFor(FakeUserRepository::FUNCTION_GET_USER_BY_ID,
            [Faker::ACTION_THROW => $exceptionThrown2 = new RuntimeException('Could not find user 2')]
        );
        $this->fakeUserRepository->addResponseFor(FakeUserRepository::FUNCTION_GET_USER_BY_ID,
            [Faker::ACTION_RETURN => self::getExampleUser()]
        );
        $this->fakeUserRepository->addResponseFor(FakeUserRepository::FUNCTION_GET_USER_BY_ID,
            [Faker::ACTION_RETURN => self::getExampleUser()]
        );
        $this->fakeUserRepository->addResponseFor(FakeUserRepository::FUNCTION_DELETE_USER,
            [Faker::ACTION_VOID => null]
        );
        // ===============
        // Not called as it failed earlier.
        // ===============
        $this->fakeUserRepository->addResponseFor(FakeUserRepository::FUNCTION_DELETE_USER,
            [Faker::ACTION_THROW => $exceptionThrown3 = new RuntimeException('Database timeout exception')]
        );
        $this->fakeUserRepository->addResponseFor(FakeUserRepository::FUNCTION_DELETE_USER,
            [Faker::ACTION_VOID => null]
        );

        $this->deleteUserService->deleteUser(2);

        $tester->expectThrowable(
            $exceptionThrown2,
            function (): void {
                $this->deleteUserService->deleteUser(2);
            }
        );

        $tester->expectThrowable(
            $exceptionThrown3,
            function (): void {
                $this->deleteUserService->deleteUser(2);
            }
        );

        $this->deleteUserService->deleteUser(2);
    }

    /** @see FakerCest::fakeCallWillReturnAndVoidAndThrowMultipleResponses */
    public function addResponseForWillAddResponsesAndWorkInNewWay(UnitTester $tester): void
    {
        $this->fakeUserRepository->addResponseFor(FakeUserRepository::FUNCTION_GET_USER_BY_ID,
            [Faker::ACTION_RETURN => self::getExampleUser()]
        );
        $this->fakeUserRepository->addResponseFor(FakeUserRepository::FUNCTION_DELETE_USER,
            [Faker::ACTION_VOID => null]
        );

        $this->deleteUserService->deleteUser(2);

        $this->fakeUserRepository->addResponseFor(FakeUserRepository::FUNCTION_GET_USER_BY_ID,
            [Faker::ACTION_THROW => $exceptionThrown2 = new RuntimeException('Could not find user 2')]
        );
        // ===============
        // Not called as it failed earlier.
        // ===============

        $tester->expectThrowable(
            $exceptionThrown2,
            function (): void {
                $this->deleteUserService->deleteUser(2);
            }
        );

        $this->fakeUserRepository->addResponseFor(FakeUserRepository::FUNCTION_GET_USER_BY_ID,
            [Faker::ACTION_RETURN => self::getExampleUser()]
        );
        $this->fakeUserRepository->addResponseFor(FakeUserRepository::FUNCTION_DELETE_USER,
            [Faker::ACTION_THROW => $exceptionThrown3 = new RuntimeException('Database timeout exception')]
        );

        $tester->expectThrowable(
            $exceptionThrown3,
            function (): void {
                $this->deleteUserService->deleteUser(2);
            }
        );

        $this->fakeUserRepository->addResponseFor(FakeUserRepository::FUNCTION_GET_USER_BY_ID,
            [Faker::ACTION_RETURN => self::getExampleUser()]
        );
        $this->fakeUserRepository->addResponseFor(FakeUserRepository::FUNCTION_DELETE_USER,
            [Faker::ACTION_VOID => null]
        );

        $this->deleteUserService->deleteUser(2);
    }

    /** @see FakerCest::fakeCallWillReturnAndVoidAndThrowMultipleResponses */
    public function addResponsesForWillAddResponses(UnitTester $tester): void
    {
        $this->fakeUserRepository->addResponsesFor(FakeUserRepository::FUNCTION_GET_USER_BY_ID, [
            [Faker::ACTION_RETURN => self::getExampleUser()],
            [Faker::ACTION_THROW => $exceptionThrown2 = new RuntimeException('Could not find user 2')],
        ]);
        $this->fakeUserRepository->addResponsesFor(FakeUserRepository::FUNCTION_GET_USER_BY_ID, [
            [Faker::ACTION_RETURN => self::getExampleUser()],
            [Faker::ACTION_RETURN => self::getExampleUser()],
        ]);
        $this->fakeUserRepository->addResponsesFor(FakeUserRepository::FUNCTION_DELETE_USER, [
            [Faker::ACTION_VOID => null],
            // Not called as it failed earlier.
        ]);
        $this->fakeUserRepository->addResponsesFor(FakeUserRepository::FUNCTION_DELETE_USER, [
            [Faker::ACTION_THROW => $exceptionThrown3 = new RuntimeException('Database timeout exception')],
            [Faker::ACTION_VOID => null],
        ]);

        $this->deleteUserService->deleteUser(2);

        $tester->expectThrowable(
            $exceptionThrown2,
            function (): void {
                $this->deleteUserService->deleteUser(2);
            }
        );

        $tester->expectThrowable(
            $exceptionThrown3,
            function (): void {
                $this->deleteUserService->deleteUser(2);
            }
        );

        $this->deleteUserService->deleteUser(2);
    }

    /** @see FakerCest::fakeCallWillReturnAndVoidAndThrowMultipleResponses */
    public function addResponsesForWillAddResponsesAndWorkInNewWay(UnitTester $tester): void
    {
        $this->fakeUserRepository->addResponsesFor(FakeUserRepository::FUNCTION_GET_USER_BY_ID, [
            [Faker::ACTION_RETURN => self::getExampleUser()],
            [Faker::ACTION_THROW => $exceptionThrown2 = new RuntimeException('Could not find user 2')],
        ]);
        $this->fakeUserRepository->addResponsesFor(FakeUserRepository::FUNCTION_DELETE_USER, [
            [Faker::ACTION_VOID => null],
            // Not called as it failed earlier.
        ]);

        $this->deleteUserService->deleteUser(2);

        $tester->expectThrowable(
            $exceptionThrown2,
            function (): void {
                $this->deleteUserService->deleteUser(2);
            }
        );

        $this->fakeUserRepository->addResponsesFor(FakeUserRepository::FUNCTION_GET_USER_BY_ID, [
            [Faker::ACTION_RETURN => self::getExampleUser()],
            [Faker::ACTION_RETURN => self::getExampleUser()],
        ]);
        $this->fakeUserRepository->addResponsesFor(FakeUserRepository::FUNCTION_DELETE_USER, [
            [Faker::ACTION_THROW => $exceptionThrown3 = new RuntimeException('Database timeout exception')],
            [Faker::ACTION_VOID => null],
        ]);

        $tester->expectThrowable(
            $exceptionThrown3,
            function (): void {
                $this->deleteUserService->deleteUser(2);
            }
        );

        $this->deleteUserService->deleteUser(2);
    }

    public function getCallsToWillReturnCallsForIndividualMethods(UnitTester $tester): void
    {
        $this->fakeCallWillReturnAndVoidAndThrowMultipleResponses($tester);

        $tester->assertSame(
            [
            ],
            $this->fakeUserRepository->getCallsTo(FakeUserRepository::FUNCTION_UPDATE_LAST_LOGIN)
        );
        $tester->assertSame(
            [
                [
                    'userId' => 2
                ],
                [
                    'userId' => 2
                ],
                [
                    'userId' => 2
                ],
                [
                    'userId' => 2
                ],
            ],
            $this->fakeUserRepository->getCallsTo(FakeUserRepository::FUNCTION_GET_USER_BY_ID)
        );
    }

    public function getAllCallsInStyleOrderedWillReturnCallsInChronologicalOrder(UnitTester $tester): void
    {
        $this->fakeCallWillReturnAndVoidAndThrowMultipleResponses($tester);

        $tester->assertSame(
            [
                FakeUserRepository::FUNCTION_GET_USER_BY_ID => [
                    [
                        'userId' => 2
                    ],
                    [
                        'userId' => 2
                    ],
                    [
                        'userId' => 2
                    ],
                    [
                        'userId' => 2
                    ],
                ],
                FakeUserRepository::FUNCTION_DELETE_USER => [
                    [
                        'userId' => 2
                    ],
                    [
                        'userId' => 2
                    ],
                    [
                        'userId' => 2
                    ],
                ],

            ],
            $this->fakeUserRepository->getAllCallsInStyleOrdered()
        );
    }

    public function getAllCallsInStyleSortedWillReturnCallsInAlphabeticalOrder(UnitTester $tester): void
    {
        $this->fakeCallWillReturnAndVoidAndThrowMultipleResponses($tester);

        $tester->assertSame(
            [
                FakeUserRepository::FUNCTION_DELETE_USER => [
                    [
                        'userId' => 2
                    ],
                    [
                        'userId' => 2
                    ],
                    [
                        'userId' => 2
                    ],
                ],
                FakeUserRepository::FUNCTION_GET_USER_BY_ID => [
                    [
                        'userId' => 2
                    ],
                    [
                        'userId' => 2
                    ],
                    [
                        'userId' => 2
                    ],
                    [
                        'userId' => 2
                    ],
                ],
            ],
            $this->fakeUserRepository->getAllCallsInStyleSorted()
        );
    }

    public function getAllCallsInStyleOrderedExceptWillReturnCallsInChronologicalOrder(UnitTester $tester): void
    {
        $this->fakeCallWillReturnAndVoidAndThrowMultipleResponses($tester);

        $tester->assertSame(
            [
                FakeUserRepository::FUNCTION_GET_USER_BY_ID => [
                    [
                        'userId' => 2
                    ],
                    [
                        'userId' => 2
                    ],
                    [
                        'userId' => 2
                    ],
                    [
                        'userId' => 2
                    ],
                ],
            ],
            // TODO: This should get a service with 3+ calls to then erase some of them.
            $this->fakeUserRepository->getAllCallsInStyleOrderedExcept([FakeUserRepository::FUNCTION_DELETE_USER])
        );
    }

    public function getAllCallsInStyleSortedExceptWillReturnCallsInAlphabeticalOrder(UnitTester $tester): void
    {
        $this->fakeCallWillReturnAndVoidAndThrowMultipleResponses($tester);

        $tester->assertSame(
            [
                FakeUserRepository::FUNCTION_GET_USER_BY_ID => [
                    [
                        'userId' => 2
                    ],
                    [
                        'userId' => 2
                    ],
                    [
                        'userId' => 2
                    ],
                    [
                        'userId' => 2
                    ],
                ],
            ],
            // TODO: This should get a service with 3+ calls to then erase some of them.
            $this->fakeUserRepository->getAllCallsInStyleSortedExcept([FakeUserRepository::FUNCTION_DELETE_USER])
        );
    }

    private static function getExampleUser(): User
    {
        return new User(
            2,
            'NotRocky',
            false,
            \Safe\DateTimeImmutable::createFromFormat(
                '!Y-m-d H:i:s',
                '2023-02-13 12:11:14',
                new DateTimeZone('Europe/Amsterdam')
            ),
        );
    }
}
