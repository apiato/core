<?php

use Apiato\Console\CommandServiceProvider;
use Apiato\Foundation\Apiato;
use Apiato\Foundation\Configuration\Factory;
use Apiato\Foundation\Configuration\Localization;
use Apiato\Foundation\Configuration\Provider;
use Apiato\Foundation\Configuration\Repository;
use Apiato\Foundation\Configuration\Routing;
use Apiato\Foundation\Configuration\Seeding;
use Apiato\Foundation\Configuration\View;
use Apiato\Generator\GeneratorsServiceProvider;
use Apiato\Macros\MacroServiceProvider;
use Workbench\App\Containers\MySection\Author\Data\Seeders\Murdered_2;
use Workbench\App\Containers\MySection\Author\Data\Seeders\Ordered_1;
use Workbench\App\Containers\MySection\Author\Data\Seeders\Unordered;
use Workbench\App\Containers\MySection\Author\Data\Seeders\Wondered_3;
use Workbench\App\Containers\MySection\Book\Data\Factories\BookFactory;
use Workbench\App\Containers\MySection\Book\Data\Repositories\BookRepository;
use Workbench\App\Containers\MySection\Book\Models\Book;
use Workbench\App\Containers\MySection\Book\Providers\BookServiceProvider;
use Workbench\App\Containers\MySection\Book\Providers\EventServiceProvider;
use Workbench\App\Ship\Providers\ShipServiceProvider;
use Workbench\App\StrayServiceProvider;

describe(class_basename(Apiato::class), function (): void {
    it('can be created with default configuration', function (): void {
        $apiato = Apiato::configure()->create();

        expect($apiato->providers())
            ->toEqualCanonicalizing([
                GeneratorsServiceProvider::class,
                MacroServiceProvider::class,
                CommandServiceProvider::class,
                ShipServiceProvider::class,
                BookServiceProvider::class,
                EventServiceProvider::class,
            ])->and($apiato->provider()->toArray())->toEqualCanonicalizing([
                GeneratorsServiceProvider::class,
                MacroServiceProvider::class,
                CommandServiceProvider::class,
                ShipServiceProvider::class,
                BookServiceProvider::class,
                EventServiceProvider::class,
            ])->and($apiato->configs())->toEqualCanonicalizing([
                shared_path('Configs/boat.php'),
                shared_path('Configs/fractal.php'),
                shared_path('Configs/repository.php'),
                app_path('Containers/MySection/Book/Configs/mySection-book.php'),
            ])->and($apiato->events())->toEqualCanonicalizing([
                shared_path('Listeners'),
                app_path('Containers/MySection/Book/Listeners'),
                app_path('Containers/MySection/Author/Listeners'),
            ])->and($apiato->commands())->toEqualCanonicalizing([
                shared_path('Commands'),
                app_path('Containers/MySection/Book/UI/CLI/Commands'),
            ])->and($apiato->helpers())->toEqualCanonicalizing([
                shared_path('Helpers/ExplosiveClass.php'),
                shared_path('Helpers/functions.php'),
                shared_path('Helpers/helpers.php'),
                app_path('Containers/MySection/Book/Helpers/functions.php'),
                app_path('Containers/MySection/Author/Helpers/helpers.php'),
            ])->and($apiato->migrations())->toEqualCanonicalizing([
                shared_path('Migrations'),
                app_path('Containers/MySection/Book/Data/Migrations'),
                app_path('Containers/Identity/User/Data/Migrations'),
                app_path('Containers/SocialInteraction/Comment/Data/Migrations'),
                app_path('Containers/SocialInteraction/Like/Data/Migrations'),
            ])->and($apiato->seeding()->seeders())->toEqualCanonicalizing([
                Workbench\App\Containers\MySection\Book\Data\Seeders\Ordered_1::class,
                Workbench\App\Containers\MySection\Book\Data\Seeders\Murdered_2::class,
                Workbench\App\Containers\MySection\Book\Data\Seeders\Wondered_3::class,
                Workbench\App\Containers\MySection\Book\Data\Seeders\Unordered::class,
                Ordered_1::class,
                Murdered_2::class,
                Wondered_3::class,
                Unordered::class,
            ])->and($apiato->localization()->paths())->toEqualCanonicalizing([
                shared_path('Languages'),
                app_path('Containers/MySection/Book/Languages'),
            ])->and($apiato->view()->paths())->toEqualCanonicalizing([
                shared_path('Views'),
                shared_path('Mails/Templates'),
                app_path('Containers/MySection/Book/UI/WEB/Views'),
                app_path('Containers/MySection/Author/Mails/Templates'),
            ])->and($apiato->routing()->webRoutes())->toEqualCanonicalizing([
                app_path('Containers/MySection/Book/UI/WEB/Routes/CreateBook.v1.public.php'),
                app_path('Containers/MySection/Book/UI/WEB/Routes/ListBooks.php'),
                app_path('Containers/MySection/Author/UI/WEB/Routes/ListAuthors.php'),
            ])->and($apiato->factory()->resolveFactoryName(Book::class))->toBe(BookFactory::class)
            ->and($apiato->repository()->resolveModelName(BookRepository::class))->toBe(Book::class);
    });

    it('can be instantiated without a path', function (): void {
        $basePath = Safe\realpath(__DIR__ . '/../../../workbench');

        $apiato = Apiato::configure()->create();

        expect($apiato->basePath())->toBe($basePath);
    });

    it('can infer base path', function (): void {
        $basePath = Safe\realpath(__DIR__ . '/../../..');

        expect(Apiato::inferBasePath())->toBe($basePath);
    });

    it('accepts and apply  routing config override', function (): void {
        $apiato = Apiato::configure()
            ->withRouting(function (Routing $routing): void {
                $routing->prefixApiUrlsWith('test/prefix/');
            })->create();

        expect($apiato->routing()->getApiPrefix())->toBe('test/prefix/');
    });

    it('accepts and apply  factory config override', function (): void {
        $apiato = Apiato::configure()
            ->withFactories(function (Factory $factory): void {
                $factory->resolveFactoryNameUsing(static fn (string $modelName): string => 'test');
            })->create();

        expect($apiato->factory()->resolveFactoryName('anything'))->toBe('test');
    });

    it('accepts and apply  repository config override', function (): void {
        $apiato = Apiato::configure()
            ->withRepositories(function (Repository $repository): void {
                $repository->resolveModelNameUsing(static fn (string $repositoryName): string => 'test');
            })->create();

        expect($apiato->repository()->resolveModelName('anything'))->toBe('test');
    });

    it('accepts and apply view config override', function (): void {
        $apiato = Apiato::configure()
            ->withViews(function (View $view): void {
                $view->buildNamespaceUsing(static fn (string $path): string => 'test');
            })->create();

        expect($apiato->view()->buildNamespaceFor('anything'))->toBe('test');
    });

    it('accepts and apply localization config override', function (): void {
        $apiato = Apiato::configure()
            ->withTranslations(function (Localization $localization): void {
                $localization->buildNamespaceUsing(static fn (string $path): string => 'test');
            })->create();

        expect($apiato->localization()->buildNamespaceFor('anything'))->toBe('test');
    });

    it('accepts and apply seeding config override', function (): void {
        $apiato = Apiato::configure()
            ->withSeeders(function (Seeding $seeding): void {
                $seeding->sortUsing(static fn (array $classMapGroupedByDirectory): array => ['test']);
            })->create();

        expect($apiato->seeding()->seeders())
            ->toHaveLength(1)->toContain('test');
    });

    it('accepts and apply provider config override', function (): void {
        $apiato = Apiato::configure()
            ->withProviders(function (Provider $provider): void {
                $provider->loadFrom(app_path());
            })->create();

        expect($apiato->providers())->toContain(StrayServiceProvider::class);

        Apiato::reset();

        $apiato = Apiato::configure()
            ->withProviders(function (Provider $provider): void {
                $provider->loadFrom(app_path('Containers'));
            })->create();

        expect($apiato->providers())->not()->toContain(StrayServiceProvider::class);

        Apiato::reset();
    });
})->covers(Apiato::class);
