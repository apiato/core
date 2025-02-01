<?php

use Apiato\Console\CommandServiceProvider;
use Apiato\Foundation\Apiato;
use Apiato\Foundation\Configuration\Factory;
use Apiato\Foundation\Configuration\Repository;
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

describe(class_basename(Apiato::class), function (): void {
    it('can be created with default configuration', function (): void {
        $config = Apiato::configure()->create();

        expect($config->providers())
            ->toEqualCanonicalizing([
                GeneratorsServiceProvider::class,
                MacroServiceProvider::class,
                CommandServiceProvider::class,
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
                app_path('Containers/SocialInteraction/Comment/Data/Migrations'),
                app_path('Containers/SocialInteraction/Like/Data/Migrations'),
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
            ])->and($config->factory()->resolveFactoryName(Book::class))->toBe(BookFactory::class)
            ->and($config->repository()->resolveModelName(BookRepository::class))->toBe(Book::class);
    });

    it('accepts factory config override', function (): void {
        $apiato = Apiato::configure()
            ->withFactories(function (Factory $factory): void {
                $factory->resolveFactoryNameUsing(static fn (string $modelName): string => 'test');
            })->create();

        expect($apiato->factory()->resolveFactoryName('anything'))->toBe('test');
    });

    it('accepts repository config override', function (): void {
        $apiato = Apiato::configure()
            ->withRepositories(function (Repository $repository): void {
                $repository->resolveModelNameUsing(static fn (string $repositoryName): string => 'test');
            })->create();

        expect($apiato->repository()->resolveModelName('anything'))->toBe('test');
    });
})->covers(Apiato::class);
