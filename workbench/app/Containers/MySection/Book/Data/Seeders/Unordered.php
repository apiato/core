<?php

namespace Workbench\App\Containers\MySection\Book\Data\Seeders;

use Apiato\Abstract\Seeders\Seeder;
use Workbench\App\Containers\MySection\Book\Models\Book;

class Unordered extends Seeder
{
    public function run(): void
    {
        Book::factory()->createOne([
            'title' => 'Author Unordered',
        ]);
    }
}
