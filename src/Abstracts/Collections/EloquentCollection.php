<?php

namespace Apiato\Core\Abstracts\Collections;

use Illuminate\Database\Eloquent\Collection;

/**
 * @method static bool containsDecodedHash(string $hashedValue, string $key = 'id')
 */
abstract class EloquentCollection extends Collection
{
}
