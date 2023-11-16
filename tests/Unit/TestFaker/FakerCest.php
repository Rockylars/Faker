<?php

declare(strict_types=1);

namespace Rocky\Faker\Tests\Unit\TestFaker;

use Exception;
use Rocky\Faker\Faker;
use Rocky\Faker\Tests\Example\User\DeleteUserService;
use Rocky\Faker\Tests\Example\User\User;
use Rocky\Faker\Tests\Example\User\UserWatcherService;
use Rocky\Faker\Tests\Fake\Shared\FakeLogger;
use Rocky\Faker\Tests\Fake\User\FakeUserChecker;
use Rocky\Faker\Tests\Fake\User\FakeUserRepository;
use Rocky\Faker\Tests\Support\UnitTester;
use RuntimeException;

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

    public function returnOrThrowFailsWhenNoResponsesAreSet(UnitTester $tester): void
    {
        $tester->expectThrowable(
            new Exception('No responses defined for Rocky\Faker\Tests\Fake\User\FakeUserRepository::getUserById, add them to the setResponsesFor'),
            function (): void {
                $this->deleteUserService->deleteUser(2);
            }
        );
    }

    public function returnOrThrowFailsWhenNotEnoughResponsesAreSet(UnitTester $tester): void
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

    public function returnOrThrowAndVoidOrThrowWillReturnAndVoidAndThrowMultipleResponses(UnitTester $tester): void
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

    public function returnOrThrowWillReturnNullValues(UnitTester $tester): void
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

    public function voidOrThrowFailsWhenNoResponsesAreSet(UnitTester $tester): void
    {
        $this->fakeUserRepository->setResponsesFor(FakeUserRepository::FUNCTION_GET_USER_BY_ID, [
            [Faker::ACTION_RETURN => self::getExampleUser()],
        ]);

        $tester->expectThrowable(
            new Exception('No responses defined for Rocky\Faker\Tests\Fake\User\FakeUserRepository::deleteUser, add them to the setResponsesFor'),
            function (): void {
                $this->deleteUserService->deleteUser(2);
            }
        );
    }

    public function voidOrThrowFailsWhenNotEnoughResponsesAreSet(UnitTester $tester): void
    {
        $this->fakeUserRepository->setResponsesFor(FakeUserRepository::FUNCTION_GET_USER_BY_ID, [
            [Faker::ACTION_RETURN => self::getExampleUser()],
            [Faker::ACTION_RETURN => self::getExampleUser()],
        ]);
        $this->fakeUserRepository->setResponsesFor(FakeUserRepository::FUNCTION_DELETE_USER, [
            [Faker::ACTION_VOID => null],
        ]);

        $this->deleteUserService->deleteUser(2);

        $tester->expectThrowable(
            new Exception('Not enough responses defined for Rocky\Faker\Tests\Fake\User\FakeUserRepository::deleteUser, add more to the setResponsesFor'),
            function (): void {
                $this->deleteUserService->deleteUser(2);
            }
        );
    }

    /** @see FakerCest::returnOrThrowAndVoidOrThrowWillReturnAndVoidAndThrowMultipleResponses */
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

    /** @see FakerCest::returnOrThrowAndVoidOrThrowWillReturnAndVoidAndThrowMultipleResponses */
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

    /** @see FakerCest::returnOrThrowAndVoidOrThrowWillReturnAndVoidAndThrowMultipleResponses */
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

    /** @see FakerCest::returnOrThrowAndVoidOrThrowWillReturnAndVoidAndThrowMultipleResponses */
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
        $this->returnOrThrowAndVoidOrThrowWillReturnAndVoidAndThrowMultipleResponses($tester);

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
        $this->returnOrThrowAndVoidOrThrowWillReturnAndVoidAndThrowMultipleResponses($tester);

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
        $this->returnOrThrowAndVoidOrThrowWillReturnAndVoidAndThrowMultipleResponses($tester);

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
        $this->returnOrThrowAndVoidOrThrowWillReturnAndVoidAndThrowMultipleResponses($tester);

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
        $this->returnOrThrowAndVoidOrThrowWillReturnAndVoidAndThrowMultipleResponses($tester);

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
            \DateTimeImmutable::createFromFormat(
                '!Y-m-d H:i:s',
                '2023-02-13 12:11:14',
                new \DateTimeZone('Europe/Amsterdam')
            ),
        );
    }
}
