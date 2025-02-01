<?php

use Apiato\Abstract\Seeders\Seeder;
use Apiato\Foundation\Apiato;
use Apiato\Foundation\Configuration\Seeding;
use Apiato\Foundation\Database\DatabaseSeeder;
use Workbench\App\Containers\Identity\User\Models\User;

describe(class_basename(DatabaseSeeder::class), function (): void {
    it('can call other seeders', function (): void {
        $seeder = new DatabaseSeeder();
        $apiato = Apiato::configure()->withSeeders(
            static fn (Seeding $seeding) => $seeding->sortUsing(
                static fn () => [
                    (new class extends Seeder {
                        public function run(): void
                        {
                            User::factory()->createOne([
                                'name' => 'ephemeral class',
                            ]);
                        }
                    })::class,
                ],
            ),
        )->create();

        $seeder->run($apiato);

        $this->assertDatabaseHas('users', [
            'name' => 'ephemeral class',
        ]);
        $this->assertDatabaseCount('users', 1);
        expect(DatabaseSeeder::class)->toExtend(Seeder::class);
    });
})->covers(DatabaseSeeder::class);
