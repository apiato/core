<?php

namespace Apiato\Foundation\Database;

use Apiato\Abstract\Seeders\Seeder;
use Apiato\Foundation\Apiato;

class DatabaseSeeder extends Seeder
{
    public function run(Apiato $apiato): void
    {
        $classes = $apiato->seeding()->seeders();

        collect($classes)->each(fn (string $class) => $this->call($class));
    }
}
