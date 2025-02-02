<?php

namespace Apiato\Support;

use Illuminate\Support\Traits\ForwardsCalls;
use Illuminate\Support\Traits\Macroable;
use Vinkla\Hashids\HashidsManager;

/**
 * @mixin HashidsManager
 */
final class HashidsManagerDecorator
{
    use ForwardsCalls, Macroable {
        __call as macroCall;
    }

    public function __construct(
        private readonly HashidsManager $manager,
    ) {
    }

    /**
     * Decode a hash id.
     */
    public function tryDecode(string $hash): int|null
    {
        $result = $this->manager->decode($hash);

        if ([] !== $result && is_int($result[0])) {
            return $result[0];
        }

        return null;
    }

    /**
     * Decode a hash id or throw an exception.
     *
     * @throws \InvalidArgumentException
     */
    public function decode(string $hash): int
    {
        $result = $this->tryDecode($hash);

        if (is_null($result)) {
            throw new \InvalidArgumentException('Invalid hash id.');
        }

        return $result;
    }

    /**
     * Encode a number.
     */
    public function tryEncode(mixed ...$numbers): string|null
    {
        $result = $this->manager->encode(...$numbers);

        if ('' === $result) {
            return null;
        }

        return $result;
    }

    /**
     * Encode a number or throw an exception.
     *
     * @throws \RuntimeException
     */
    public function encode(mixed ...$numbers): string
    {
        $result = $this->tryEncode(...$numbers);

        if (is_null($result)) {
            throw new \RuntimeException('Encoding failed.');
        }

        return $result;
    }

    /**
     * Dynamically pass method calls to the underlying resource.
     *
     * @param string $method
     * @param array $parameters
     */
    public function __call($method, $parameters)
    {
        if (self::hasMacro($method)) {
            return $this->macroCall($method, $parameters);
        }

        return $this->forwardDecoratedCallTo($this->manager, $method, $parameters);
    }
}
