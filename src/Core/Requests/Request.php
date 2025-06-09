<?php

declare(strict_types=1);

namespace Apiato\Core\Requests;

use Illuminate\Foundation\Http\FormRequest as LaravelRequest;
use Illuminate\Support\Arr;
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
     * Get the "decode" property.
     *
     * @return string[]
     */
    public function getDecode(): array
    {
        return $this->decode;
    }

    /** @inheritDoc */
    #[\Override]
    public function route($param = null, $default = null)
    {
        if (\in_array($param, $this->decode, true) && config('apiato.hash-id')) {
            $value = parent::route($param);

            if (\is_null($value)) {
                return $default;
            }

            return hashids()->decodeOrFail($value);
        }

        return parent::route($param, $default);
    }

    /** @inheritDoc */
    #[\Override]
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
                    Arr::set($data, $dotKey, hashids()->decodeOrFail($value));
                    break;
                }
            }
        }

        return data_get($data, $key, $default);
    }
}
