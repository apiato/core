<?php

use Apiato\Abstract\Seeders\Seeder;
use Apiato\Foundation\Apiato;
use Apiato\Foundation\Database\DatabaseSeeder;
use Mockery\MockInterface;
use Tests\Support\Doubles\Fakes\Laravel\app\Containers\MySection\Book\Data\Seeders\Wondered_3;

describe(class_basename(DatabaseSeeder::class), function (): void {
    it('can call seeders', function (): void {
        $seeder = new DatabaseSeeder();
        $apiato = Mockery::mock(Apiato::class, static function (MockInterface $mock) {
            $mock->expects('seeding->seeders')->andReturn([
                Wondered_3::class,
            ]);
        });

        $seeder->run($apiato);

        $this->assertDatabaseHas('books', [
            'title' => 'Testing DatabaseSeeder',
        ]);
        $this->assertDatabaseCount('books', 1);
        expect(DatabaseSeeder::class)->toExtend(Seeder::class);
    });
})->covers(DatabaseSeeder::class);
