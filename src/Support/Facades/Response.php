<?php

namespace Apiato\Support\Facades;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Facade;
use League\Fractal\Serializer\SerializerAbstract;
use League\Fractal\TransformerAbstract;

/**
 * @method static \Apiato\Http\Response create(mixed $data = null, callable|TransformerAbstract|null|string $transformer = null, SerializerAbstract|null|string $serializer = null)
 * @method static JsonResponse ok(mixed $data = null, array $headers = [], int $options = 0)
 * @method static JsonResponse created(mixed $data = null, array $headers = [], int $options = 0)
 * @method static JsonResponse noContent(array $headers = [], int $options = 0)
 * @method static JsonResponse accepted(mixed $data = null, array $headers = [], int $options = 0)
 * @method static JsonResponse respond(callable|int $statusCode = 200, callable|array $headers = [], callable|int $options = 0)
 * @method static string|callable|TransformerAbstract|null getTransformer()
 * @method static void macro(string $name, object|callable $macro)
 * @method static void mixin(object $mixin, bool $replace = true)
 * @method static bool hasMacro(string $name)
 * @method static void flushMacros()
 *
 * @see \Apiato\Http\Response
 */
class Response extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Apiato\Http\Response::class;
    }
}
