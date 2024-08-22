<?php

namespace Apiato\Core\Facades;

use Illuminate\Http\JsonResponse;
use League\Fractal\Serializer\SerializerAbstract;
use League\Fractal\TransformerAbstract;
use Spatie\Fractal\Facades\Fractal;

/**
 * @method static \Apiato\Core\Services\Response createFrom(mixed $data = null, callable|TransformerAbstract|null $transformer = null, SerializerAbstract|null $serializer = null)
 * @method static JsonResponse ok()
 * @method static JsonResponse created()
 * @method static JsonResponse noContent()
 * @method static JsonResponse accepted()
 * @method static string|callable|TransformerAbstract|null getTransformer()
 * @method static void macro(string $name, object|callable $macro)
 * @method static void mixin(object $mixin, bool $replace = true)
 * @method static bool hasMacro(string $name)
 * @method static void flushMacros()
 */
class Response extends Fractal
{
}
