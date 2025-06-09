<?php

declare(strict_types=1);

use Apiato\Foundation\Configuration\View;
use Illuminate\Support\Str;

describe(class_basename(View::class), function (): void {
    it('creates different namespaces for shared directory and container paths', function (): void {
        $configuration = new View();

        expect(
            $configuration->buildNamespaceFor(shared_path('Views')),
        )->toBe(Str::of(shared_path())->afterLast(DIRECTORY_SEPARATOR)->camel()->value())
            ->and(
                $configuration->buildNamespaceFor(
                    app_path('Containers/MySection/Book/Views'),
                ),
            )->toBe('mySection@book');
    });

    it('can set views paths', function (): void {
        $configuration = new View();

        $configuration->loadFrom(
            shared_path('Views'),
            shared_path('Mails'),
            app_path('Containers/MySection/Book/Views'),
            app_path('Containers/MySection/Book/Mails'),
        );

        expect($configuration->paths())->toBe([
            shared_path('Views'),
            shared_path('Mails'),
            app_path('Containers/MySection/Book/Views'),
            app_path('Containers/MySection/Book/Mails'),
        ]);
    });
})->covers(View::class);
