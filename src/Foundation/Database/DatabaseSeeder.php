<?php

declare(strict_types=1);

namespace Apiato\Foundation\Database;

use Apiato\Core\Seeders\Seeder;
use Apiato\Foundation\Apiato;
use Illuminate\Support\Facades\DB;

final class DatabaseSeeder extends Seeder
{
    public function run(Apiato $apiato): void
    {
        $classes = $apiato->seeding()->seeders();

        /**
         * @var class-string<Seeder> $class
         * @var Seeder               $this
         */
        collect($classes)->each(fn (string $class) => $class::WITH_TRANSACTIONS
            ? DB::transaction(fn (string $class) => $this->call($class))
            : $this->call($class));
    }
}
