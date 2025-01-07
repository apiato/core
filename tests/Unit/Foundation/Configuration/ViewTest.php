<?php

use Apiato\Foundation\Configuration\View;
use Apiato\Foundation\Support\PathHelper;

describe(class_basename(View::class), function (): void {
    it('creates different namespaces for shared directory and container paths', function (): void {
        $configuration = new View();

        expect(
            $configuration->buildNamespaceFor(PathHelper::getSharedDirectoryPath() . '/Views'),
        )->toBe('ship')
            ->and(
                $configuration->buildNamespaceFor(
                    __DIR__ . '/../../../Support/Doubles/Fakes/Laravel/app/Containers/MySection/Book/Views',
                ),
            )->toBe('mySection@book');
    });

    it('can set views paths', function (): void {
        $configuration = new View();

        $configuration->loadFrom(
            __DIR__ . '/../../../Support/Doubles/Fakes/Laravel/app/Ship/Views',
            __DIR__ . '/../../../Support/Doubles/Fakes/Laravel/app/Ship/Mails',
            __DIR__ . '/../../../Support/Doubles/Fakes/Laravel/app/Containers/MySection/Book/Views',
            __DIR__ . '/../../../Support/Doubles/Fakes/Laravel/app/Containers/MySection/Book/Mails',
        );

        expect($configuration->paths())->toBe([
            __DIR__ . '/../../../Support/Doubles/Fakes/Laravel/app/Ship/Views',
            __DIR__ . '/../../../Support/Doubles/Fakes/Laravel/app/Ship/Mails',
            __DIR__ . '/../../../Support/Doubles/Fakes/Laravel/app/Containers/MySection/Book/Views',
            __DIR__ . '/../../../Support/Doubles/Fakes/Laravel/app/Containers/MySection/Book/Mails',
        ]);
    });
})->covers(View::class);
