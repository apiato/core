<?php

namespace Workbench\App\Containers\MySection\Author\Data\Seeders;

use Apiato\Abstract\Seeders\Seeder;
use Workbench\App\Containers\MySection\Book\Data\Factories\BookFactory;

class Wondered_3 extends Seeder
{
    public function run(): void
    {
        BookFactory::new()->createOne([
            'title' => '3',
        ]);
    }
}
