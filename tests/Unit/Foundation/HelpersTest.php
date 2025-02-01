<?php

use Apiato\Foundation\Apiato;
use Vinkla\Hashids\HashidsManager;

describe('helpers', function (): void {
    it('can get the Apiato instance', function (): void {
        expect(apiato())->toBeInstanceOf(Apiato::class);
    })->coversFunction('apiato');

    it('can get the path to the application\'s shared directory', function (): void {
        expect(shared_path())->toBe(base_path('app/Ship'));
    })->coversFunction('shared_path');

    it('can get the Hashids instance', function (): void {
        expect(hashids())->toBeInstanceOf(HashidsManager::class);
    })->coversFunction('hashids');
});
