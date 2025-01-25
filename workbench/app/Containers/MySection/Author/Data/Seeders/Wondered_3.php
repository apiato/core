<?php

namespace Workbench\App\Containers\MySection\Author\Data\Seeders;

use Apiato\Abstract\Seeders\Seeder;
use Workbench\App\Containers\MySection\Book\Models\Book;

class Wondered_3 extends Seeder
{
    public function run(): void
    {
        Book::factory()->createOne([
            'title' => '3',
        ]);
    }
}
