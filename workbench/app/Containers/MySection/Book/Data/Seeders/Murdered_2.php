<?php

namespace Workbench\App\Containers\MySection\Book\Data\Seeders;

use Apiato\Abstract\Seeders\Seeder;
use Workbench\App\Containers\MySection\Book\Data\Factories\BookFactory;

class Murdered_2 extends Seeder
{
    public function run(): void
    {
        BookFactory::new()->createOne([
            'title' => '2',
        ]);
    }
}
