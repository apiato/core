<?php

namespace Workbench\App\Containers\MySection\Book\Data\Seeders;

use Apiato\Abstract\Seeders\Seeder;
use Workbench\App\Containers\MySection\Book\Data\Factories\BookFactory;

class Unordered extends Seeder
{
    public function run(): void
    {
        BookFactory::new()->createOne([
            'title' => 'Author Unordered',
        ]);
    }
}
