<?php

use Apiato\Abstract\Actions\Action;
use Workbench\App\Containers\Identity\User\Models\User;

describe(class_basename(Action::class), function (): void {
    it('can run a transactional action', function (): void {
        $action = new class extends Action {
            public static function run(string $name, string $email): void
            {
                User::factory()->createOne([
                    'name' => $name,
                    'email' => $email,
                ]);

                expect(User::count())->toBe(1)
                    ->and(User::first())->name->toBe($name)
                    ->email->toBe($email);

                throw new RuntimeException('Rollback');
            }
        };

        expect(static function () use ($action): void {
            $action->transactionalRun('John Doe', 'john@doe.examples');
        })->toThrow(RuntimeException::class)
            ->and(User::count())->toBe(0);
    });
})->covers(Action::class);
