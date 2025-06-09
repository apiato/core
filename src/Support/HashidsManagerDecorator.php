<?php

declare(strict_types=1);

namespace Apiato\Support;

use Illuminate\Support\Traits\ForwardsCalls;
use Illuminate\Support\Traits\Macroable;
use Vinkla\Hashids\HashidsManager;
use Webmozart\Assert\Assert;

/**
 * @mixin HashidsManager
 */
final class HashidsManagerDecorator
{
    use ForwardsCalls, Macroable {
        __call as macroCall;
    }

    public function __construct(private readonly HashidsManager $manager)
    {
    }

    /**
     * Decode a hashed id.
     *
     * @return int|int[]|null
     */
    public function decode(string $hash): null|int|array
    {
        $result = $this->manager->decode($hash);

        if (\count($result) === 1 && \is_int($result[0])) {
            return $result[0];
        }

        if (\count($result) > 1) {
            return $result;
        }

        return null;
    }

    /**
     * Decode a hash id or an array of hash ids (recursively).
     *
     * @return int|int[]
     *
     * @throws \InvalidArgumentException
     */
    public function decodeOrFail(string ...$hash): int|array
    {
        if (\count($hash) > 1) {
            Assert::allStringNotEmpty($hash);

            return array_map(fn (string $id): int|array => $this->decodeOrFail($id), $hash);
        }

        Assert::stringNotEmpty($hash[0]);

        $result = $this->decode($hash[0]);

        if (\is_null($result)) {
            throw new \InvalidArgumentException('Invalid hash id.');
        }

        return $result;
    }

    public function encode(string|int ...$numbers): null|string
    {
        $result = $this->manager->encode(...$numbers);

        if ($result === '') {
            return null;
        }

        return $result;
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function encodeOrFail(mixed ...$numbers): string
    {
        $result = $this->encode(...$numbers);

        if (\is_null($result)) {
            throw new \InvalidArgumentException('Encoding failed.');
        }

        return $result;
    }

    /**
     * Dynamically pass method calls to the underlying resource.
     *
     * @param string $method
     * @param array  $parameters
     */
    public function __call($method, $parameters)
    {
        if (self::hasMacro($method)) {
            return $this->macroCall($method, $parameters);
        }

        return $this->forwardDecoratedCallTo($this->manager, $method, $parameters);
    }
}
