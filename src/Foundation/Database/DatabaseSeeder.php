<?php

namespace Apiato\Foundation\Database;

use Apiato\Core\Seeders\Seeder;
use Apiato\Foundation\Apiato;

final class DatabaseSeeder extends Seeder
{
    public function run(Apiato $apiato): void
    {
        $classes = $apiato->seeding()->seeders();

        collect($classes)->each(fn (string $class) => $this->call($class));
    }
}
