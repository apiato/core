<?php

declare(strict_types=1);

use Apiato\Foundation\Support\Providers\HelperServiceProvider;

describe(class_basename(HelperServiceProvider::class), function (): void {
    it('can load helper files', function (): void {
        expect(function_exists('this_is_a_test_function_to_test_functions_file'))->toBeTrue()
            ->and(function_exists('this_is_a_test_function_to_test_helpers_file'))->toBeTrue()
            ->and(function_exists('this_is_a_container_test_function_to_test_helpers_file'))->toBeTrue()
            ->and(function_exists('this_is_a_container_test_function_to_test_functions_file'))->toBeTrue();
    });
})->covers(HelperServiceProvider::class);
