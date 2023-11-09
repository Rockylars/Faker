<?php

declare(strict_types=1);

namespace Rocky\Faker\Tests\Unit\TestFaker;

use Exception;
use Rocky\Faker\Faker;
use Rocky\Faker\Tests\Fake\NotFakeButNeededForExample\User\DeleteUserService;
use Rocky\Faker\Tests\Fake\NotFakeButNeededForExample\User\User;
use Rocky\Faker\Tests\Fake\Shared\FakeLogger;
use Rocky\Faker\Tests\Fake\User\FakeUserRepository;
use Rocky\Faker\Tests\Support\UnitTester;
use RuntimeException;

final class FakerCest
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

    public function returnOrThrowWillReturnAndThrow(UnitTester $tester): void
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

    /** @skip */
    public function returnOrThrowWillReturnNullValues(UnitTester $tester): void
    {
        // TODO: Add test for returning null. Requires a new service and new tests for that service.
    }

    // TODO: Add tests for all the call retrievals.

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
