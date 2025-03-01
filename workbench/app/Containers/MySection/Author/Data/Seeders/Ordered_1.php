<?php

namespace Workbench\App\Containers\MySection\Author\Data\Seeders;

use Apiato\Core\Seeders\Seeder;
use Workbench\App\Containers\MySection\Book\Models\Book;

class Ordered_1 extends Seeder
{
    public function run(): void
    {
        Book::factory()->createOne([
            'title' => '1',
        ]);
    }
}
