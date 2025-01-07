<?php

use Apiato\Foundation\Configuration\Seeding;
use Tests\Support\Doubles\Fakes\Laravel\app\Containers\MySection\Book\Data\Seeders\Murdered_2;
use Tests\Support\Doubles\Fakes\Laravel\app\Containers\MySection\Book\Data\Seeders\Ordered_1;
use Tests\Support\Doubles\Fakes\Laravel\app\Containers\MySection\Book\Data\Seeders\Unordered;
use Tests\Support\Doubles\Fakes\Laravel\app\Containers\MySection\Book\Data\Seeders\Wondered_3;

describe(class_basename(Seeding::class), function (): void {
    it('can load sorted seeder classes from paths', function (): void {
        $configuration = new Seeding();

        $configuration->loadFrom(
            __DIR__ . '/../../../Support/Doubles/Fakes/Laravel/app/Containers/MySection/Book/Data/Seeders',
            __DIR__ . '/../../../Support/Doubles/Fakes/Laravel/app/Containers/MySection/Author/Data/Seeders',
        );

        expect($configuration->seeders())->toBe([
            Ordered_1::class,
            Murdered_2::class,
            Wondered_3::class,
            Unordered::class,
            \Tests\Support\Doubles\Fakes\Laravel\app\Containers\MySection\Author\Data\Seeders\Ordered_1::class,
            \Tests\Support\Doubles\Fakes\Laravel\app\Containers\MySection\Author\Data\Seeders\Murdered_2::class,
            \Tests\Support\Doubles\Fakes\Laravel\app\Containers\MySection\Author\Data\Seeders\Wondered_3::class,
            \Tests\Support\Doubles\Fakes\Laravel\app\Containers\MySection\Author\Data\Seeders\Unordered::class,
        ]);
    });
})->covers(Seeding::class);
