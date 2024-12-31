<?php

namespace Tests\Infrastructure\Fakes\Laravel\app\Ship\Parents\Collections;

use Apiato\Core\Abstracts\Collections\EloquentCollection as AbstractEloquentCollection;
use Illuminate\Database\Eloquent\Model;

/**
 * @template TKey of array-key
 * @template TModel of Model
 *
 * @extends AbstractEloquentCollection<TKey, TModel>
 */
abstract class EloquentCollection extends AbstractEloquentCollection
{
}
