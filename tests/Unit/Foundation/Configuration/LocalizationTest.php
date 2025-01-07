<?php

use Apiato\Foundation\Configuration\Localization;
use Apiato\Foundation\Support\PathHelper;

describe(class_basename(Localization::class), function (): void {
    it('creates different namespaces for shared directory and container paths', function (): void {
        $configuration = new Localization();

        expect(
            $configuration->buildNamespaceFor(PathHelper::getSharedDirectoryPath() . '/Languages'),
        )->toBe('ship')
            ->and(
                $configuration->buildNamespaceFor(
                    __DIR__ . '/../../../Support/Doubles/Fakes/Laravel/app/Containers/MySection/Book/Languages',
                ),
            )->toBe('mySection@book');
    });

    it('can set translation paths', function (): void {
        $configuration = new Localization();

        $configuration->loadFrom(
            __DIR__ . '/../../../Support/Doubles/Fakes/Laravel/app/Ship/Languages',
            __DIR__ . '/../../../Support/Doubles/Fakes/Laravel/app/Containers/MySection/Book/Languages',
        );

        expect($configuration->paths())->toBe([
            __DIR__ . '/../../../Support/Doubles/Fakes/Laravel/app/Ship/Languages',
            __DIR__ . '/../../../Support/Doubles/Fakes/Laravel/app/Containers/MySection/Book/Languages',
        ]);
    });
})->covers(Localization::class);
