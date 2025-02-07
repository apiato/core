<?php

namespace Apiato\Abstract\Requests;

use Apiato\Abstract\Models\UserModel as User;
use Illuminate\Foundation\Http\FormRequest as LaravelRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;

abstract class Request extends LaravelRequest
{
    /**
     * Id's that needs decoding before applying the validation rules.
     *
     * @example ['id', 'author_ids.*', 'nested.id', 'nested.ids.*', 'nested.*.id']
     *
     * @var string[]
     */
    protected array $decode = [];

    /**
     * To be used mainly from unit tests.
     */
    public static function injectData(array $parameters = [], User|null $user = null, array $cookies = [], array $files = [], array $server = []): static
    {
        // if user is passed, will be returned when asking for the authenticated user using `\Auth::user()`
        if ($user instanceof User) {
            $app = App::getInstance();
            $app['auth']->guard($driver = 'api')->setUser($user);
            $app['auth']->shouldUse($driver);
        }

        // For now doesn't matter which URI or Method is used.
        $request = parent::create('/', 'GET', $parameters, $cookies, $files, $server);

        $request->setUserResolver(static fn (): User|null => $user);

        return $request;
    }

    /**
     * Add properties to the request that are not part of the request body
     * but are needed for the request to be processed.
     * For example, in the unit tests, we can add the url parameters to the request which is not part of the request body.
     * It is best used with the `injectData` method.
     *
     * @param array<string, mixed> $properties
     */
    public function withUrlParameters(array $properties): static
    {
        foreach ($properties as $key => $value) {
            $this->{$key} = $value;
        }

        return $this;
    }

    /**
     * Get the decode array.
     *
     * @return string[]
     */
    public function getDecodeArray(): array
    {
        return $this->decode;
    }

    public function route($param = null, $default = null)
    {
        if (in_array($param, $this->decode, true) && config('apiato.hash-id')) {
            $value = parent::route($param);

            if (is_null($value)) {
                return $default;
            }

            return hashids()->decode($value);
        }

        return parent::route($param, $default);
    }

    public function input($key = null, $default = null)
    {
        if (!config('apiato.hash-id')) {
            return parent::input($key, $default);
        }

        $data = parent::input();

        $flattened = Arr::dot($data);

        foreach ($flattened as $dotKey => $value) {
            foreach ($this->decode as $pattern) {
                if (Str::is($pattern, $dotKey)) {
                    Arr::set($data, $dotKey, hashids()->decode($value));
                    break;
                }
            }
        }

        return data_get($data, $key, $default);
    }
}
