<?php

declare(strict_types=1);

namespace Apiato\Core\Collections;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * @template TKey of array-key
 * @template TModel of Model
 *
 * @extends Collection<TKey, TModel>
 *
 * @method static bool containsDecodedHash(string $hashedValue, string $key = 'id')
 */
abstract class EloquentCollection extends Collection
{
}
