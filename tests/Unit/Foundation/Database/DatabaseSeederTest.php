<?php

use Apiato\Abstract\Seeders\Seeder;
use Apiato\Foundation\Apiato;
use Apiato\Foundation\Database\DatabaseSeeder;
use Mockery\MockInterface;
use Tests\Support\Doubles\Fakes\Laravel\app\Containers\MySection\Book\Data\Factories\BookFactory;

describe(class_basename(DatabaseSeeder::class), function (): void {
it('can call seeders', function (): void {
        $seeder = new DatabaseSeeder();
        $apiato = $this->mock(Apiato::class, function (MockInterface $mock) {
            $mock->expects('seeding->seeders')->andReturn([
                (new class extends Seeder
                {
                    public function run(): void
                    {
                        BookFactory::new()->createOne([
                            'title' => 'Testing DatabaseSeeder',
                        ]);
                    }
                })::class,
            ]);
        });

        $seeder->run($apiato);

        $this->assertDatabaseHas('books', [
            'title' => 'Testing DatabaseSeeder',
        ]);
        expect(DatabaseSeeder::class)->toExtend(Seeder::class);
    });
})->covers(DatabaseSeeder::class);
