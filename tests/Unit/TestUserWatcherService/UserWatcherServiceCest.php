<?php

declare(strict_types=1);

namespace Rocky\Faker\Tests\Unit\TestUserWatcherService;

use Rocky\Faker\Faker;
use Rocky\Faker\Tests\Fake\NotFakeButNeededForExample\User\User;
use Rocky\Faker\Tests\Fake\NotFakeButNeededForExample\User\UserWatcherService;
use Rocky\Faker\Tests\Fake\User\FakeUserChecker;
use Rocky\Faker\Tests\Fake\User\FakeUserRepository;
use Rocky\Faker\Tests\Support\UnitTester;

final class UserWatcherServiceCest
{
    private UserWatcherService $userWatcherService;
    private FakeUserChecker $fakeUserChecker;
    private FakeUserRepository $fakeUserRepository;

    public function _before(UnitTester $tester): void
    {
        $this->userWatcherService = new UserWatcherService(
            $this->fakeUserChecker = new FakeUserChecker(),
            $this->fakeUserRepository = new FakeUserRepository()
        );
    }

    public function userObservedWillDoItsThing(UnitTester $tester): void
    {
        $this->fakeUserChecker->setResponsesFor(FakeUserChecker::FUNCTION_TEST_METHOD_THAT_MIGHT_RETURN_NULL, [
            [Faker::ACTION_RETURN => $expected = null],
        ]);
        $this->fakeUserRepository->setResponsesFor(FakeUserRepository::FUNCTION_UPDATE_LAST_LOGIN, [
            [Faker::ACTION_VOID => null],
        ]);

        $result = $this->userWatcherService->userObserved(1);
        $tester->assertSame($result, $expected);

        $tester->assertSame(
            [
                FakeUserChecker::FUNCTION_TEST_METHOD_THAT_MIGHT_RETURN_NULL => [
                    [
                        'a call was made'
                    ]
                ]
            ],
            $this->fakeUserChecker->getAllCallsInStyleSorted()
        );
        $tester->assertSame(
            [
                FakeUserRepository::FUNCTION_UPDATE_LAST_LOGIN => [
                    [
                        'userId' => 1
                    ]
                ]
            ],
            $this->fakeUserRepository->getAllCallsInStyleSorted()
        );
    }

    public function userObservedWillRecordThatWithUserSent(UnitTester $tester): void
    {
        $this->fakeUserChecker->setResponsesFor(FakeUserChecker::FUNCTION_TEST_METHOD_THAT_MIGHT_RETURN_NULL, [
            [Faker::ACTION_RETURN => $expected = 27],
        ]);
        $this->fakeUserRepository->setResponsesFor(FakeUserRepository::FUNCTION_UPDATE_LAST_LOGIN, [
            [Faker::ACTION_VOID => null],
        ]);

        $result = $this->userWatcherService->userObserved(self::getExampleAdminUser());
        $tester->assertSame($result, $expected);

        $tester->assertSame(
            [
                FakeUserChecker::FUNCTION_TEST_METHOD_THAT_MIGHT_RETURN_NULL => [
                    [
                        'a call was made'
                    ]
                ]
            ],
            $this->fakeUserChecker->getAllCallsInStyleSorted()
        );
        $tester->assertSame(
            [
                FakeUserRepository::FUNCTION_UPDATE_LAST_LOGIN => [
                    [
                        'userId' => 1
                    ]
                ]
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
}