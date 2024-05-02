<?php

declare(strict_types=1);

namespace Rocky\Faker;

use Exception;

/**
 * Faker? I think you're the fake hedgehog around here. You're comparing yourself to me? Ha! You're not even good enough
 * to be my fake.
 */
abstract class Faker
{
    public const ACTION_RETURN = 'return';
    public const ACTION_THROW = 'throw';
    public const ACTION_VOID = 'void';
    public const ACTION_FUNCTION = 'function';

    /** @var array<string, array<int, array<string|int, mixed>>> */
    private array $callsToPerFunction = [];
    /** @var array<string, int> */
    private array $iterationsOfCallsToPerFunction = [];
    /** @var array<string, array<int, array<string, mixed>>> */
    private array $responsesForCallsToGetPerFunction = [];

    /** @param array<string|int, mixed> $input */
    final protected function returnOrThrow(string $faked, array $input, array $arguments = []): mixed
    {
        $this->callsToPerFunction[$faked][] = $input;
        if (!array_key_exists($faked, $this->iterationsOfCallsToPerFunction)) {
            throw new Exception('No responses defined for ' . static::class . '::' . $faked . ', add them to the setResponsesFor');
        }
        $response = $this->responsesForCallsToGetPerFunction[$faked][$this->iterationsOfCallsToPerFunction[$faked]++] ?? throw new Exception('Not enough responses defined for ' . static::class . '::' . $faked . ', add more to the setResponsesFor');
        if (array_key_exists(self::ACTION_RETURN, $response)) {
            return $response[self::ACTION_RETURN];
        } elseif (array_key_exists(self::ACTION_FUNCTION, $response)) {
            return $response[self::ACTION_FUNCTION](...$arguments);
        } elseif (array_key_exists(self::ACTION_THROW, $response)) {
            throw $response[self::ACTION_THROW];
        } else {
            throw new Exception('Unknown response type');
        }
    }

    /** @param array<string|int, mixed> $input */
    final protected function voidOrThrow(string $faked, array $input, array $arguments = []): void
    {
        $this->callsToPerFunction[$faked][] = $input;
        if (!array_key_exists($faked, $this->iterationsOfCallsToPerFunction)) {
            throw new Exception('No responses defined for ' . static::class . '::' . $faked . ', add them to the setResponsesFor');
        }
        $response = $this->responsesForCallsToGetPerFunction[$faked][$this->iterationsOfCallsToPerFunction[$faked]++] ?? throw new Exception('Not enough responses defined for ' . static::class . '::' . $faked . ', add more to the setResponsesFor');
        if (array_key_exists(self::ACTION_VOID, $response)) {
            return;
        } elseif (array_key_exists(self::ACTION_FUNCTION, $response)) {
            $response[self::ACTION_FUNCTION](...$arguments);
        } elseif (array_key_exists(self::ACTION_THROW, $response)) {
            throw $response[self::ACTION_THROW];
        } else {
            throw new Exception('Unknown response type');
        }
    }

    /** @param array<int, array<string, mixed>> $responses */
    final public function setResponsesFor(string $faked, array $responses): void
    {
        if (!method_exists(static::class, $faked)) {
            throw new Exception(static::class . '::' . $faked . ' does not exist');
        }
        $this->iterationsOfCallsToPerFunction[$faked] = 0;
        $this->responsesForCallsToGetPerFunction[$faked] = $responses;
    }

    /** @param array<string, mixed> $response */
    final public function addResponseFor(string $faked, array $response): void
    {
        if (!method_exists(static::class, $faked)) {
            throw new Exception(static::class . '::' . $faked . ' does not exist');
        }
        $this->iterationsOfCallsToPerFunction[$faked] ??= 0;
        $this->responsesForCallsToGetPerFunction[$faked][] = $response;
    }

    /** @param array<int, array<string, mixed>> $responses */
    final public function addResponsesFor(string $faked, array $responses): void
    {
        if (!method_exists(static::class, $faked)) {
            throw new Exception(static::class . '::' . $faked . ' does not exist');
        }
        $this->iterationsOfCallsToPerFunction[$faked] ??= 0;
        $this->responsesForCallsToGetPerFunction[$faked] = array_key_exists($faked, $this->responsesForCallsToGetPerFunction)
            ? array_merge($this->responsesForCallsToGetPerFunction[$faked], $responses)
            : $responses;
    }

    /** @return array<int, array<string|int, mixed>> */
    final public function getCallsTo(string $faked): array
    {
        return $this->callsToPerFunction[$faked] ?? [];
    }

    /** @return array<string, array<int, array<string|int, mixed>>> */
    final public function getAllCallsInStyleOrdered(): array
    {
        return $this->callsToPerFunction;
    }

    /** @return array<string, array<int, array<string|int, mixed>>> */
    final public function getAllCallsInStyleSorted(): array
    {
        $callsToPerFunction = $this->callsToPerFunction;
        ksort($callsToPerFunction, SORT_STRING);
        return $callsToPerFunction;
    }

    /**
     * @param array<int, string> $keysToIgnore
     * @return array<string, array<int, array<string|int, mixed>>>
     */
    final public function getAllCallsInStyleOrderedExcept(array $keysToIgnore): array
    {
        $callsToPerFunction = $this->callsToPerFunction;
        foreach ($keysToIgnore as $keyToIgnore) {
            unset($callsToPerFunction[$keyToIgnore]);
        }
        return $callsToPerFunction;
    }

    /**
     * @param array<int, string> $keysToIgnore
     * @return array<string, array<int, array<string|int, mixed>>>
     */
    final public function getAllCallsInStyleSortedExcept(array $keysToIgnore): array
    {
        $callsToPerFunction = $this->callsToPerFunction;
        foreach ($keysToIgnore as $keyToIgnore) {
            unset($callsToPerFunction[$keyToIgnore]);
        }
        ksort($callsToPerFunction, SORT_STRING);
        return $callsToPerFunction;
    }
}
