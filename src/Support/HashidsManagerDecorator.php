<?php

namespace Apiato\Support;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
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

    public function __construct(
        private readonly HashidsManager $manager,
    ) {
    }

    public function tryDecode(string $hash): int|null
    {
        $result = $this->manager->decode($hash);

        if ([] !== $result && is_int($result[0])) {
            return $result[0];
        }

        return null;
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function decode(string $hash): int
    {
        Assert::stringNotEmpty($hash);

        $result = $this->tryDecode($hash);

        if (is_null($result)) {
            throw new \InvalidArgumentException('Invalid hash id.');
        }

        return $result;
    }

    public function tryEncode(mixed ...$numbers): string|null
    {
        $result = $this->manager->encode(...$numbers);

        if ('' === $result) {
            return null;
        }

        return $result;
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function encode(mixed ...$numbers): string
    {
        $result = $this->tryEncode(...$numbers);

        if (is_null($result)) {
            throw new \InvalidArgumentException('Encoding failed.');
        }

        return $result;
    }

    /**
     * @param string[] $hash
     *
     * @return int[]
     *
     * @throws \InvalidArgumentException
     */
    public function decodeArray(array $hash): array
    {
        Assert::allStringNotEmpty($hash);

        return array_map(fn ($id) => $this->decode($id), $hash);
    }

    /**
     * without decoding the encoded id's you won't be able to use
     * validation features like `exists:table,id`.
     */
    public function decodeFields(array $source, array $keys): array
    {
        $flattened = Arr::dot($source);

        foreach ($keys as $pattern) {
            $flattened = collect($flattened)->mapWithKeys(function ($value, $dotKey) use ($pattern) {
                if (Str::is($pattern, $dotKey)) {
                    if (empty($value)) {
                        return [$dotKey => $value];
                    }

                    if (!is_string($value)) {
                        throw new \RuntimeException("String expected, got " . gettype($value));
                    }

                    $decoded = hashids()->tryDecode($value);
                    if (is_null($decoded)) {
                        throw new \RuntimeException("ID ({$dotKey}) is incorrect, consider using the hashed ID.");
                    }
                    return [$dotKey => $decoded];
                }
                return [$dotKey => $value];
            })->all();
        }

        return Arr::undot($flattened);
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
