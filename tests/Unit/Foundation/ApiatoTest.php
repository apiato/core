<?php

use Apiato\Foundation\Apiato;
use Apiato\Foundation\Configuration\Localization;
use Apiato\Foundation\Middleware\ProcessETag;
use Apiato\Foundation\Middleware\Profiler;
use Apiato\Foundation\Middleware\ValidateJsonContent;
use Apiato\Foundation\Support\Providers\LocalizationServiceProvider;
use Workbench\App\Containers\MySection\Author\Data\Seeders\Murdered_2;
use Workbench\App\Containers\MySection\Author\Data\Seeders\Ordered_1;
use Workbench\App\Containers\MySection\Author\Data\Seeders\Unordered;
use Workbench\App\Containers\MySection\Author\Data\Seeders\Wondered_3;
use Workbench\App\Containers\MySection\Book\Providers\BookServiceProvider;
use Workbench\App\Containers\MySection\Book\Providers\EventServiceProvider;
use Workbench\App\Ship\Providers\ShipServiceProvider;

describe(class_basename(Apiato::class), function (): void {
    it('can be created with default configuration', function (): void {
        $config = Apiato::configure(__DIR__)->create();
        expect($config->providers())->toEqualCanonicalizing([
            ShipServiceProvider::class,
            BookServiceProvider::class,
            EventServiceProvider::class,
        ])->and($config->configs())->toEqualCanonicalizing([
            shared_path('Configs/boat.php'),
            app_path('Containers/MySection/Book/Configs/mySection-book.php'),
        ])->and($config->events())->toEqualCanonicalizing([
            shared_path('Listeners'),
            app_path('Containers/MySection/Book/Listeners'),
            app_path('Containers/MySection/Author/Listeners'),
        ])->and($config->commands())->toEqualCanonicalizing([
            shared_path('Commands'),
            app_path('Containers/MySection/Book/UI/CLI'),
        ])->and($config->helpers())->toEqualCanonicalizing([
            shared_path('Helpers/ExplosiveClass.php'),
            shared_path('Helpers/functions.php'),
            shared_path('Helpers/helpers.php'),
            app_path('Containers/MySection/Book/Helpers/functions.php'),
            app_path('Containers/MySection/Author/Helpers/helpers.php'),
        ])->and($config->migrations())->toEqualCanonicalizing([
            shared_path('Migrations'),
            app_path('Containers/MySection/Book/Data/Migrations'),
            app_path('Containers/Identity/User/Data/Migrations'),
        ])->and($config->seeding()->seeders())->toEqualCanonicalizing([
            Workbench\App\Containers\MySection\Book\Data\Seeders\Ordered_1::class,
            Workbench\App\Containers\MySection\Book\Data\Seeders\Murdered_2::class,
            Workbench\App\Containers\MySection\Book\Data\Seeders\Wondered_3::class,
            Workbench\App\Containers\MySection\Book\Data\Seeders\Unordered::class,
            Ordered_1::class,
            Murdered_2::class,
            Wondered_3::class,
            Unordered::class,
        ])->and($config->localization()->paths())->toEqualCanonicalizing([
            shared_path('Languages'),
            app_path('Containers/MySection/Book/Languages'),
        ])->and($config->view()->paths())->toEqualCanonicalizing([
            shared_path('Views'),
            shared_path('Mails/Templates'),
            app_path('Containers/MySection/Book/UI/WEB/Views'),
            app_path('Containers/MySection/Author/Mails/Templates'),
        ])->and($config->routing()->webRoutes())->toEqualCanonicalizing([
            app_path('Containers/MySection/Book/UI/WEB/Routes/CreateBook.v1.public.php'),
            app_path('Containers/MySection/Book/UI/WEB/Routes/ListBooks.php'),
            app_path('Containers/MySection/Author/UI/WEB/Routes/ListAuthors.php'),
        ]);
    });

    it('can be configured via a closure to customize translation namespaces', function (): void {
        Apiato::configure()
            ->withTranslations(function (Localization $localization): void {
                $localization->buildNamespaceUsing(static fn (string $path): string => 'test');
            })->create();

        app()->register(LocalizationServiceProvider::class, true);

        $this->app->setLocale('fa');
        expect(__('test::errors.forbidden'))->toBe('ممنوع');
    });

    it('can list Core middlewares', function (): void {
        $middlewares = [
            ValidateJsonContent::class,
            ProcessETag::class,
            Profiler::class,
        ];

        expect($this->app->make(Apiato::class)->apiMiddlewares())
            ->toBe($middlewares);
    });
})->covers(Apiato::class);
