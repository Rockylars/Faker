### Introduction
Welcome to the world of single service testing with my favorite little project, Faker.
It isn't much, but it will do what you need, simply create a class and make it extend Faker and implement the service's interface.
I recommend creating a separate folder in your tests folder called "fake" to keep them from getting lost in your test files.
I don't recommend using them in functional tests, just integration and unit tests.

### History
Faker is something I've made through in class methods around March of 2023 while working at [Future500](https://future500.nl/) on a project for my former company, [FinanceMatters](https://www.financematters.nl/)/[BondCenter](https://www.bondcenter.nl/), great guys in both of them.
Over the next month more functions were added and due to the size of some classes, the methods were copied over to simplify the procedure.
With PHP 8 turning throws into statements, the code was very compact, but some more testing revealed it needed to be a bit less compressed.
Months passed and due to unfortunate circumstances out of my control, my partnership with the companies ended a 5 year long relationship.
Luckily, the people there are really kind and also were sadly not *that* interested in the fun i've had with Faker, so they allowed me to keep it and distribute it as a package.

So here it is, the framework I've built because I highly disliked working with [prophesizing](https://github.com/phpspec/prophecy) and found [mocking](https://github.com/mockery/mockery) to be too convoluted and not simple enough.
This simple abstract class allows you to build fake classes to your heart's content, without PHPStan ever yelling at you.
They are made to be as simple as possible with a later addition of some `getAllCalls..` methods to not technically require a new check whenever you add a new method.

### Usage
For unit tests, I recommend faking everything, but some classes shouldn't be faked and instead be kept as half active.
Such half active classes are for example a fake clock with a `updateTime()` method inside.

For integration tests, only fake what you need to fake, don't fake things like the connection when you are testing a repository, but you can then use fake things like a Guzzle client.
On that note, don't actually use Faker for your logger, you will have many calls to logger but will already be testing the output and don't care much for a duplicate input.
For a logger, it's best to just array collect it and "fake" it like that, other classes like that where you don't own the deeper layers should get the same array collection treatment.
However, with all that said, you do you.


### Future
I will add a method that adds a count check between the amount of responses set up and the amount of calls done, similar to something Mockery offers.
This is rather useful of course if you have a lot of responses or set up data but missed that you didn't actually see anything go in, happens when you have a lot of fake classes set up for one service.

### Examples
```php
final class FakeUserRepository extends Faker implements UserRepository
{
    public const FUNCTION_GET_USER = 'getUser';
    public const FUNCTION_GET_USERS = 'getUsers';
    public const FUNCTION_IS_ACTIVE = 'isActive';
    public const FUNCTION_UPDATE_LAST_LOGIN = 'updateLastLogin';
    public const FUNCTION_DELETE_USER = 'deleteUser';

    public function getUserById(int $userId): User
    {
        return $this->returnOrThrow(__FUNCTION__, [
            'userId' => $userId,
        ]);
    }

    /** @return array<int, User> */
    public function getUsers(): array
    {
        return $this->returnOrThrow(__FUNCTION__, [
            'a call was made',
        ]);
    }

    public function isActive(int $userId): bool
    {
        return $this->returnOrThrow(__FUNCTION__, [
            'userId' => $userId,
        ]);
    }

    public function updateLastLogin(int $userId): void
    {
        return $this->voidOrThrow(__FUNCTION__, [
            'userId' => $userId,
        ]);
    }

    public function deleteUser(int $userId): void
    {
        return $this->voidOrThrow(__FUNCTION__, [
            'userId' => $userId,
        ]);
    }
}
```


```php
final class DeleteUserServiceCest
{
    private DeleteUserService $deleteUserService;
    private FakeLogger $fakeLogger;
    private FakeUserRepository $fakeUserRepository;

    public function _before(IntegrationTester $tester): void
    {
        $this->deleteUserService = new DeleteUserService(
            $this->fakeLogger = new FakeLogger(),
            $this->fakeUserRepository = new FakeUserRepository()
        );
    }

    public function deleteUserWillCheckAndDeleteUserByIdIfNothingIsLinked(IntegrationTester $tester): void
    {
        $this->fakeUserRepository->setResponsesFor(FakeUserRepository::FUNCTION_GET_USER, [
            [Faker::ACTION_RETURN => new User(
                1,
                'Rocky',
                true,
                DateTimeImmutable::createFromFormat(
                    '!Y-m-d H:i:s',
                    '2023-02-17 12:13:14',
                    new \DateTimeZone('Europe/Amsterdam')
                ),
            )],
        ]);
        $this->fakeUserRepository->setResponsesFor(FakeUserRepository::FUNCTION_DELETE_USER, [
            [Faker::ACTION_VOID => null],
        ]);

        $this->deleteUserService->deleteUser(1);

        $tester->assertSame(
            [
                [
                    'level' => 'debug',
                    'message' => 'User 1 was deleted',
                    'context' => [],
                ],
            ],
            $this->fakeLogger->getLogs(),
        );
        $tester->assertSame(
            [
                FakeUserRepository::FUNCTION_GET_USER => [
                    [
                        'userId' => 1,
                    ],
                ],
                FakeUserRepository::FUNCTION_GET_USER => [
                    [
                        'userId' => 1,
                    ],
                ],
            ],
            $this->fakeUserRepository->getAllCallsInStyleSorted()
        );
    }
}
```

