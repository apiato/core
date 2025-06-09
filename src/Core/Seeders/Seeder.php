<?php

declare(strict_types=1);

namespace Apiato\Core\Seeders;

use Illuminate\Database\Seeder as LaravelSeeder;

abstract class Seeder extends LaravelSeeder
{
    /**
     * Indicates if the seeder should wrap the seeding in a transaction.
     * This is useful for speeding up the seeding process, as it allows
     * the database to perform bulk inserts instead of individual inserts.
     */
    public const WITH_TRANSACTIONS = false;
}
