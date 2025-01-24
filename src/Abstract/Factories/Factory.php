<?php

namespace Apiato\Abstract\Factories;

use Illuminate\Database\Eloquent\Factories\Factory as LaravelFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @template TModel of Model
 *
 * @extends LaravelFactory<TModel>
 */
abstract class Factory extends LaravelFactory
{
}
