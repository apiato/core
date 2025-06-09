<?php

declare(strict_types=1);

use Apiato\Foundation\Configuration\Localization;
use Illuminate\Support\Str;

describe(class_basename(Localization::class), function (): void {
    it('creates different namespaces for shared directory and container paths', function (): void {
        $configuration = new Localization();

        expect(
            $configuration->buildNamespaceFor(shared_path('Languages')),
        )->toBe(Str::of(shared_path())->afterLast(DIRECTORY_SEPARATOR)->camel()->value())
            ->and(
                $configuration->buildNamespaceFor(
                    app_path('Containers/MySection/Book/Languages'),
                ),
            )->toBe('mySection@book');
    });

    it('can set translation paths', function (): void {
        $configuration = new Localization();

        $configuration->loadFrom(
            shared_path('Languages'),
            app_path('Containers/MySection/Book/Languages'),
        );

        expect($configuration->paths())->toBe([
            shared_path('Languages'),
            app_path('Containers/MySection/Book/Languages'),
        ]);
    });
})->covers(Localization::class);
