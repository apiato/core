<?php

use Apiato\Foundation\Configuration\Localization;
use Apiato\Foundation\Support\PathHelper;

describe(class_basename(Localization::class), function (): void {
    describe('provides a default namespace builder function', function (): void {
        it('creates different namespaces for shared directory and container paths', function (): void {
            $localization = new Localization();

            $namespace = $localization->buildNamespaceFor(
                PathHelper::getSharedDirectoryPath() . '/Languages',
            );

            expect($namespace)->toBe('ship');

            $namespace = $localization->buildNamespaceFor(
                __DIR__ . '/../../../Support/Doubles/Fakes/Laravel/app/Containers/MySection/Book/Languages',
            );

            expect($namespace)->toBe('mySection@book');
        });
    });

    it('can set translation paths', function (): void {
        $localization = new Localization();

        $localization->loadTranslationsFrom(
            __DIR__ . '/../../../Support/Doubles/Fakes/Laravel/app/Ship/Languages',
            __DIR__ . '/../../../Support/Doubles/Fakes/Laravel/app/Containers/MySection/Book/Languages',
        );

        expect($localization->paths())->toBe([
            __DIR__ . '/../../../Support/Doubles/Fakes/Laravel/app/Ship/Languages',
            __DIR__ . '/../../../Support/Doubles/Fakes/Laravel/app/Containers/MySection/Book/Languages',
        ]);
    });
})->covers(Localization::class);
