<?php

use Apiato\Foundation\Loaders\HelperLoader;

describe(class_basename(HelperLoader::class), function (): void {
    it('can load helper files', function (): void {
        $loader = HelperLoader::create(__DIR__ . '/../../../Support/Doubles/Fakes/Laravel/app/Ship/Helpers');
        $loader->load();

        expect(function_exists('this_is_a_test_function_to_test_functions_file'))->toBeTrue()
            ->and(function_exists('this_is_a_test_function_to_test_helpers_file'))->toBeTrue();
    });
})->covers(HelperLoader::class);
