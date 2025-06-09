<?php

declare(strict_types=1);

use Apiato\Foundation\Configuration\Seeding;
use Workbench\App\Containers\MySection\Book\Data\Seeders\Murdered_2;
use Workbench\App\Containers\MySection\Book\Data\Seeders\Ordered_1;
use Workbench\App\Containers\MySection\Book\Data\Seeders\Unordered;
use Workbench\App\Containers\MySection\Book\Data\Seeders\Wondered_3;

describe(class_basename(Seeding::class), function (): void {
    it('can load sorted seeder classes from paths', function (): void {
        $configuration = new Seeding();

        $configuration->loadFrom(
            app_path('Containers/MySection/Book/Data/Seeders'),
            app_path('Containers/MySection/Author/Data/Seeders'),
        );

        expect($configuration->seeders())->toBe([
            Ordered_1::class,
            Murdered_2::class,
            Wondered_3::class,
            Unordered::class,
            Workbench\App\Containers\MySection\Author\Data\Seeders\Ordered_1::class,
            Workbench\App\Containers\MySection\Author\Data\Seeders\Murdered_2::class,
            Workbench\App\Containers\MySection\Author\Data\Seeders\Wondered_3::class,
            Workbench\App\Containers\MySection\Author\Data\Seeders\Unordered::class,
        ]);
    });
})->covers(Seeding::class);
