<?php

namespace Workbench\App\Containers\MySection\Book\Data\Seeders;

use Apiato\Abstract\Seeders\Seeder;
use Workbench\App\Containers\MySection\Book\Models\Book;

class Murdered_2 extends Seeder
{
    public function run(): void
    {
        Book::factory()->createOne([
            'title' => '2',
        ]);
    }
}
