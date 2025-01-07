<?php

namespace Tests\Support\Doubles\Fakes\Laravel\app\Containers\MySection\Book\Data\Seeders;

use Apiato\Abstract\Seeders\Seeder;
use Tests\Support\Doubles\Fakes\Laravel\app\Containers\MySection\Book\Data\Factories\BookFactory;

class Unordered extends Seeder
{
    public function run(): void
    {
        BookFactory::new()->createOne([
            'title' => 'Author Unordered',
        ]);
    }
}
