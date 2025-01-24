<?php

use Apiato\Abstract\Seeders\Seeder;
use Apiato\Foundation\Apiato;
use Apiato\Foundation\Configuration\Seeding;
use Apiato\Foundation\Database\DatabaseSeeder;
use Workbench\App\Containers\MySection\Book\Data\Factories\BookFactory;

describe(class_basename(DatabaseSeeder::class), function (): void {
    it('can call other seeders', function (): void {
        $seeder = new DatabaseSeeder();
        $apiato = Apiato::configure()->withSeeders(
            static fn (Seeding $seeding) => $seeding->sortUsing(
                static fn () => [
                    (new class extends Seeder {
                        public function run(): void
                        {
                            BookFactory::new()->createOne([
                                'title' => 'ephemeral class',
                            ]);
                        }
                    })::class,
                ],
            ),
        )->create();

        $seeder->run($apiato);

        $this->assertDatabaseHas('books', [
            'title' => 'ephemeral class',
        ]);
        $this->assertDatabaseCount('books', 1);
        expect(DatabaseSeeder::class)->toExtend(Seeder::class);
    });
})->covers(DatabaseSeeder::class);
